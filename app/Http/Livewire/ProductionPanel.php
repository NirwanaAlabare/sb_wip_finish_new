<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Undo;
use App\Models\Nds\OutputPacking;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class ProductionPanel extends Component
{
    // Data
    public $orderDate;
    public $orderInfo;
    public $orderWsDetails;
    public $orderWsDetailSizes;
    public $outputRft;
    public $outputDefect;
    public $outputReject;
    public $outputRework;
    public $outputFiltered;

    // Filter
    public $selectedColor;
    public $selectedColorName;
    public $selectedSize;

    // Panel views
    public $panels;
    public $rft;
    public $defect;
    public $defectHistory;
    public $reject;
    public $rework;

    // Undo
    public $undoSizes;
    public $undoType;
    public $undoQty;
    public $undoSize;
    public $undoDefectType;
    public $undoDefectArea;

    // Rules
    protected $rules = [
        'undoType' => 'required',
        'undoQty' => 'required|numeric|min:1',
        'undoSize' => 'required',
    ];

    protected $messages = [
        'undoType.required' => 'Terjadi kesalahan, tipe undo output tidak terbaca.',
        'undoQty.required' => 'Harap tentukan kuantitas undo output.',
        'undoQty.numeric' => 'Harap isi kuantitas undo output dengan angka.',
        'undoQty.min' => 'Kuantitas undo output tidak bisa kurang dari 1.',
        'undoSize.required' => 'Harap tentukan ukuran undo output.',
    ];

    // Event listeners
    protected $listeners = [
        'toProductionPanel' => 'toProductionPanel',
        'toRft' => 'toRft',
        'toDefect' => 'toDefect',
        'toDefectHistory' => 'toDefectHistory',
        'toReject' => 'toReject',
        'toRework' => 'toRework',
        'countRft' => 'countRft',
        'countDefect' => 'countDefect',
        'countReject' => 'countReject',
        'countRework' => 'countRework',
        'preSubmitUndo' => 'preSubmitUndo',
        'updateOrder' => 'updateOrder',
    ];

    public function mount(SessionManager $session, $orderInfo, $orderWsDetails)
    {
        $this->orderInfo = $orderInfo;
        $this->orderWsDetails = $orderWsDetails;

        // Put data on session
        $session->put("orderInfo", $orderInfo);
        $session->put("orderWsDetails", $orderWsDetails);

        // Default value
        $this->selectedColor = $this->orderWsDetails[0]->id;
        $this->selectedColorName = $this->orderWsDetails[0]->color;
        $this->selectedSize = 'all';
        $this->panels = true;
        $this->rft = false;
        $this->defect = false;
        $this->defectHistory = false;
        $this->reject = false;
        $this->rework = false;
        $this->outputRft = 0;
        $this->outputDefect = 0;
        $this->outputReject = 0;
        $this->outputRework = 0;
        $this->outputFiltered = 0;
        $this->undoType = "";
        $this->undoQty = 1;
        $this->undoSize = "";
        $this->undoDefectType = "";
        $this->undoDefectArea = "";

        $this->orderWsDetailSizes = MasterPlan::selectRaw("
                MIN(so_det.id) as so_det_id,
                so_det.size as size
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->where('master_plan.sewing_line', str_replace(" ", "_", $this->orderInfo->sewing_line))
            ->where('act_costing.kpno', $this->orderInfo->ws_number)
            ->where('so_det.color', $this->selectedColorName)
            ->where('master_plan.cancel', 'N')
            ->where('so_det.cancel', 'N')
            ->groupBy('so_det.size')
            ->orderBy('so_det_id')
            ->get();

        $session->put("orderWsDetailSizes", $this->orderWsDetailSizes);
    }

    public function toRft()
    {
        $this->panels = false;
        $this->rft = !($this->rft);
        $this->emit('toInputPanel', 'rft');
    }

    public function toDefect()
    {
        $this->panels = false;
        $this->defect = !($this->defect);
        $this->emitTo('defect','clearInput');
        $this->emitTo('defect','updateOutput');
        $this->emit('toInputPanel', 'defect');
    }

    public function toDefectHistory()
    {
        $this->panels = false;
        $this->defectHistory = !($this->defectHistory);
        $this->emit('toInputPanel', 'defect');
    }

    public function toReject()
    {
        $this->panels = false;
        $this->reject = !($this->reject);
        $this->emit('toInputPanel', 'reject');
    }

    public function toRework()
    {
        $this->panels = false;
        $this->rework = !($this->rework);
        $this->emit('toInputPanel', 'rework');
    }

    public function toProductionPanel()
    {
        $this->panels = true;
        $this->rft = false;
        $this->defect = false;
        $this->defectHistory = false;
        $this->reject = false;
        $this->rework = false;
        $this->emit('fromInputPanel');
    }

    public function preSubmitUndo($undoType)
    {
        $this->undoQty = 1;
        $this->undoSize = '';
        $this->undoType = $undoType;

        $this->emit('showModal', 'undo');
    }

    public function submitUndo()
    {
        $validatedData = $this->validate();

        $size = DB::select(DB::raw("SELECT * FROM so_det WHERE id = '".$this->undoSize."'"));
        $defectType = DefectType::select('defect_type')->find($this->undoDefectType);
        $defectArea = DefectArea::select('defect_area')->find($this->undoDefectArea);

        switch ($this->undoType) {
            case 'rft' :
                // Undo RFT
                $rftSql = Rft::where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    where('created_by', Auth::user()->username)->
                    where('status', 'NORMAL')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    take($this->undoQty);
                $rftNdsSql = OutputPacking::where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    where('created_by', Auth::user()->username)->
                    where('status', 'NORMAL')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    take($this->undoQty);

                $getRfts = $rftSql->get();

                foreach ($getRfts as $getRft) {
                    $addUndoHistory = Undo::create([
                        'master_plan_id' => $getRft->master_plan_id,
                        'so_det_id' => $getRft->so_det_id,
                        'output_rft_id' => $getRft->id,
                        'keterangan' => 'rft',
                    ]);
                }

                $deleteRft = $rftSql->delete();
                $deleteRftNds = $rftNdsSql->delete();

                if ($deleteRft)  {
                    $this->emit('alert', 'success', 'Output RFT dengan ukuran '.$size[0]->size.' berhasil di UNDO sebanyak '.$deleteRft.' kali.');

                    $this->emit('hideModal', 'undo');
                } else {
                    $this->emit('alert', 'error', 'Output RFT dengan ukuran '.$size[0]->size.' gagal di UNDO.');
                }

                break;
            case 'defect' :
                // Undo DEFECT
                $defectQuery = Defect::selectRaw('output_defects_packing.id as defect_id, output_defects_packing.*')->
                    leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects_packing.defect_area_id')->
                    leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    where('defect_status', 'defect');
                if ($this->undoDefectType) {
                    $defectQuery->where('output_defects_packing.defect_type_id', $this->undoDefectType);
                };
                if ($this->undoDefectArea) {
                    $defectQuery->where('output_defects_packing.defect_area_id', $this->undoDefectArea);
                };
                $defectQuery->orderBy('output_defects_packing.updated_at', 'DESC')->
                    orderBy('output_defects_packing.created_at', 'DESC')->
                    take($this->undoQty);

                $getDefects = $defectQuery->get();

                foreach ($getDefects as $getDefect) {
                    $addUndoHistory = Undo::create([
                        'master_plan_id' => $getDefect->master_plan_id,
                        'so_det_id' => $getDefect->so_det_id,
                        'output_defect_id' => $getDefect->defect_id,
                        'keterangan' => 'defect',
                    ]);

                    $deleteDefect = Defect::find($getDefect->id)->delete();
                }

                $defectTypeText = $defectType ? ' dengan defect type = '.$defectType->defect_type : '';
                $defectAreaText = $defectArea ? 'dengan defect area = '.$defectArea->defect_area.' ' : '';

                if ($getDefects->count() > 0) {
                    $this->emit('alert', 'success', 'Output DEFECT dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'berhasil di UNDO sebanyak '.$getDefects->count().' kali.');

                    $this->emit('hideModal', 'undo');
                } else {
                    $this->emit('alert', 'error', 'Output DEFECT dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'gagal di UNDO.');
                }

                break;
            case 'reject' :
                // Undo REJECT
                $rejectSql = Reject::where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    take($this->undoQty);

                $getRejects = $rejectSql->get();

                foreach ($getRejects as $reject) {
                    $addUndoHistory = Undo::create([
                        'master_plan_id' => $reject->master_plan_id,
                        'so_det_id' => $reject->so_det_id,
                        'output_reject_id' => $reject->id,
                        'keterangan' => 'reject',
                    ]);
                }

                $deleteReject = $rejectSql->delete();

                if ($deleteReject) {
                    $this->emit('alert', 'success', 'Output REJECT dengan ukuran '.$size[0]->size.' berhasil di UNDO sebanyak '.$deleteReject.' kali.');

                    $this->emit('hideModal', 'undo');
                } else {
                    $this->emit('alert', 'error', 'Output REJECT dengan ukuran '.$size[0]->size.' gagal di UNDO.');
                }

                break;
            case 'rework' :
                // Undo REWORK
                $defectQuery = Defect::selectRaw('output_defects_packing.id as defect_id, output_defects_packing.*')->
                    leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects_packing.defect_area_id')->
                    leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    where('defect_status', 'reworked');
                if ($this->undoDefectType) {
                    $defectQuery->where('output_defects_packing.defect_type_id', $this->undoDefectType);
                }
                if ($this->undoDefectArea) {
                    $defectQuery->where('output_defects_packing.defect_area_id', $this->undoDefectArea);
                }
                $getDefects = $defectQuery->orderBy('output_defects_packing.updated_at', 'DESC')->
                    orderBy('output_defects_packing.created_at', 'DESC')->
                    limit($this->undoQty)->
                    get();

                // update defect & delete rework
                foreach ($getDefects as $defect) {
                    Undo::create(['master_plan_id' => $defect->master_plan_id, 'so_det_id' => $defect->so_det_id, 'output_rework_id' => $defect->rework->id, 'keterangan' => 'rework',]);
                    Defect::where('id', $defect->defect_id)->update(['defect_status' => 'defect']);
                    Rft::leftJoin('output_reworks_packing', 'output_reworks_packing.id', '=', 'output_rfts_packing.rework_id')->where('output_reworks_packing.defect_id', $defect->defect_id)->delete();
                    Rework::where('defect_id', $defect->defect_id)->delete();
                }

                $defectTypeText = $defectType ? ' dengan defect type = '.$defectType->defect_type : '';
                $defectAreaText = $defectArea ? 'dengan defect area = '.$defectArea->defect_area.' ' : '';

                if ($getDefects->count() > 0) {
                    $this->emit('alert', 'success', 'Output REWORK dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'berhasil di UNDO sebanyak '.$getDefects->count().' kali.');

                    $this->emit('hideModal', 'undo');
                } else {
                    $this->emit('alert', 'error', 'Output REWORK dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'gagal di UNDO.');
                }

                break;
        }
    }

    public function updateOrder() {
        $this->emit('loadingStart');

        $this->selectedSize = 'all';

        $this->orderInfo = MasterPlan::selectRaw("
                master_plan.id as id,
                master_plan.tgl_plan as tgl_plan,
                REPLACE(master_plan.sewing_line, '_', ' ') as sewing_line,
                master_plan.id_ws as id_ws,
                act_costing.kpno as ws_number,
                act_costing.styleno as style_name,
                mastersupplier.supplier as buyer_name,
                so_det.styleno_prod as reff_number,
                master_plan.color as color,
                so_det.size as size,
                so.qty as qty_order,
                CONCAT(masterproduct.product_group, ' - ', masterproduct.product_item) as product_type
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
            ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
            ->where('so_det.cancel', 'N')
            ->where('master_plan.id', $this->selectedColor)
            ->first();

        $this->orderWsDetails = MasterPlan::selectRaw("
                master_plan.id as id,
                master_plan.tgl_plan as tgl_plan,
                master_plan.color as color,
                mastersupplier.supplier as buyer_name,
                act_costing.styleno as style_name,
                mastersupplier.supplier as buyer_name
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
            ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
            ->where('so_det.cancel', 'N')
            ->where('master_plan.sewing_line', str_replace(" ", "_", $this->orderInfo->sewing_line))
            ->where('act_costing.kpno', $this->orderInfo->ws_number)
            ->where('master_plan.tgl_plan', $this->orderInfo->tgl_plan)
            ->groupBy(
                'master_plan.id',
                'master_plan.tgl_plan',
                'master_plan.color',
                'mastersupplier.supplier',
                'act_costing.styleno',
                'mastersupplier.supplier'
            )->get();

        $this->orderWsDetailSizes = MasterPlan::selectRaw("
                MIN(so_det.id) as so_det_id,
                so_det.size as size
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->where('master_plan.sewing_line', str_replace(" ", "_", $this->orderInfo->sewing_line))
            ->where('act_costing.kpno', $this->orderInfo->ws_number)
            ->where('so_det.color', $this->selectedColorName)
            ->where('master_plan.cancel', "N")
            ->where('so_det.cancel', "N")
            ->groupBy('so_det.size')
            ->orderBy('so_det_id')
            ->get();

        session()->put("orderInfo", $this->orderInfo);
        session()->put("orderWsDetails", $this->orderWsDetails);
        session()->put("orderWsDetailSizes", $this->orderWsDetailSizes);

        $this->emit('updateWsDetailSizes');
    }

    public function deleteRedundant() {
        $redundantData = DB::select(DB::raw(
            "select defect_id, jml from (select defect_id, COUNT(defect_id) jml from (SELECT a.* from output_reworks_packing a inner join output_defects_packing c on c.id = a.defect_id inner join master_plan b on b.id = c.master_plan_id where ".(Auth::user()->Groupp != 'ALLSEWING' ? "b.sewing_line = '".strtoupper(Auth::user()->username)."' AND " : "")." DATE_FORMAT(a.created_at, '%Y-%m-%d') = CURRENT_DATE() order by a.defect_id asc) a GROUP BY a.defect_id) a where a.jml > 1"
        ));

        foreach ($redundantData as $redundant) {
            $reworkData = Rework::where('defect_id', $redundant->defect_id)->limit(1)->first();
            Rework::where('id', $reworkData->id)->limit(1)->delete();
            RftModel::where('rework_id', $reworkData->id)->limit(1)->delete();
        }

        $this->emit('alert', 'success', 'Redundant deleted');
    }

    public function render(SessionManager $session)
    {
        // Keep this data with session
        $this->orderInfo = $session->get("orderInfo", $this->orderInfo);
        $this->orderWsDetails = $session->get("orderWsDetails", $this->orderWsDetails);
        $this->orderWsDetailSizes = $session->get("orderWsDetailSizes", $this->orderWsDetailSizes);

        $this->orderDate = $this->orderInfo->tgl_plan;

        // Get total output
        $masterPlan = MasterPlan::selectRaw("
            GROUP_CONCAT(DISTINCT rfts.output) output_rft,
            GROUP_CONCAT(DISTINCT defects.output) output_defect,
            GROUP_CONCAT(DISTINCT reworks.output) output_rework,
            GROUP_CONCAT(DISTINCT rejects.output) output_reject
        ")->
        leftJoin(
            DB::raw("
                (
                    select
                        master_plan.id master_plan_id,
                        count(output_rfts_packing.id) output
                    from
                        output_rfts_packing
                    left join
                        master_plan on master_plan.id = output_rfts_packing.master_plan_id
                    where
                        master_plan.id = '".$this->orderInfo->id."'
                        and output_rfts_packing.status = 'NORMAL'
                    group by
                        master_plan.id
                ) rfts
            "), 'rfts.master_plan_id', '=', 'master_plan.id'
        )->
        leftJoin(
            DB::raw("
                (
                    select
                        master_plan.id master_plan_id,
                        count(output_defects_packing.id) output
                    from
                        output_defects_packing
                    left join
                        master_plan on master_plan.id = output_defects_packing.master_plan_id
                    where
                        master_plan.id = '".$this->orderInfo->id."'
                        and output_defects_packing.defect_status = 'defect'
                    group by
                        master_plan.id
                ) defects
            "), 'defects.master_plan_id', '=', 'master_plan.id'
        )->
        leftJoin(
            DB::raw("
                (
                    select
                        master_plan.id master_plan_id,
                        count(output_defects_packing.id) output
                    from
                        output_defects_packing
                    left join
                        master_plan on master_plan.id = output_defects_packing.master_plan_id
                    where
                        master_plan.id = '".$this->orderInfo->id."'
                        and output_defects_packing.defect_status = 'reworked'
                    group by
                        master_plan.id
                ) reworks
            "), 'reworks.master_plan_id', '=', 'master_plan.id'
        )->
        leftJoin(
            DB::raw("
                (
                    select
                        master_plan.id master_plan_id,
                        count(output_rejects_packing.id) output
                    from
                        output_rejects_packing
                    left join
                        master_plan on master_plan.id = output_rejects_packing.master_plan_id
                    where
                        master_plan.id = '".$this->orderInfo->id."'
                    group by
                        master_plan.id
                ) rejects
            "), 'rejects.master_plan_id', '=', 'master_plan.id'
        )->
        where('master_plan.id', $this->orderInfo->id)->
        groupBy('master_plan.id', 'rfts.master_plan_id', 'defects.master_plan_id', 'reworks.master_plan_id', 'rejects.master_plan_id')->
        first();

        $this->outputRft = $masterPlan->output_rft ? $masterPlan->output_rft : 0;
        $this->outputDefect = $masterPlan->output_defect ? $masterPlan->output_defect : 0;
        $this->outputRework = $masterPlan->output_rework ? $masterPlan->output_rework : 0;
        $this->outputReject = $masterPlan->output_reject ? $masterPlan->output_reject : 0;
        $soDet = DB::table('so_det')->where('id', $this->selectedSize)->first();
        $sqlFiltered = Rft::select('id')->leftJoin('so_det', 'so_det.id', '=', 'output_rfts_packing.so_det_id')->where('master_plan_id', $this->orderInfo->id)->where('status', 'NORMAL');
        $this->outputFiltered = $this->selectedSize == 'all' ? $sqlFiltered->count() : $sqlFiltered->where('so_det.size', $soDet->size)->count();

        // Undo size data
        switch ($this->undoType) {
            case 'rft' :
                $this->undoSizes = Rft::selectRaw('so_det.id as so_det_id, so_det.size, count(*) as total')->
                    leftJoin('so_det', 'so_det.id', '=', 'output_rfts_packing.so_det_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('status', 'NORMAL')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    groupBy('so_det.id', 'so_det.size')->
                    get();
                break;
            case 'defect' :
                $this->undoSizes = Defect::selectRaw('so_det.id as so_det_id, so_det.size, count(*) as total')->
                    leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('status', 'NORMAL')->
                    where('defect_status', 'defect')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    groupBy('so_det.id', 'so_det.size')->
                    get();
                break;
            case 'reject' :
                $this->undoSizes = Reject::selectRaw('so_det.id as so_det_id, so_det.size, count(*) as total')->
                    leftJoin('so_det', 'so_det.id', '=', 'output_rejects_packing.so_det_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('status', 'NORMAL')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    groupBy('so_det.id', 'so_det.size')->
                    get();
                break;
            case 'rework' :
                $this->undoSizes = Rework::selectRaw('so_det.id as so_det_id, so_det.size, count(*) as total')->
                    leftJoin('output_defects_packing', 'output_defects_packing.id', '=', 'output_reworks_packing.defect_id')->
                    leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
                    where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
                    where('output_reworks_packing.status', 'NORMAL')->
                    orderBy('output_reworks_packing.updated_at', 'DESC')->
                    orderBy('output_reworks_packing.created_at', 'DESC')->
                    groupBy('so_det.id', 'so_det.size')->
                    get();
                break;
        }

        // Defect
        $undoDefectTypes = DefectType::all();
        $undoDefectAreas = DefectArea::all();

        return view('livewire.production-panel', ['undoDefectTypes' => $undoDefectTypes, 'undoDefectAreas' => $undoDefectAreas]);
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }
}
