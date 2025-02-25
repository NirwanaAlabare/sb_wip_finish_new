<?php

namespace App\Http\Livewire\Scan;

use Livewire\Component;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\OutputFinishing;
use App\Models\SignalBit\Undo;
use App\Models\SignalBit\UndoFinishing;
use App\Models\Nds\Numbering;
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

    // Input
    public $scannedNumberingCode;
    public $scannedNumberingInput;
    public $scannedSizeInput;
    public $scannedSizeInputText;

    public $baseUrl;

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

        $this->baseUrl = url('/');

        $this->orderWsDetailSizes = DB::table('master_plan')->selectRaw("
                MIN(so_det.id) as so_det_id,
                so_det.size as size,
                so_det.dest as dest,
                CONCAT(so_det.size, (CASE WHEN so_det.dest != '-' OR so_det.dest IS NULL THEN CONCAT('-', so_det.dest) ELSE '' END)) as size_dest
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->where('master_plan.sewing_line', str_replace(" ", "_", $this->orderInfo->sewing_line))
            ->where('act_costing.kpno', $this->orderInfo->ws_number)
            ->where('so_det.color', $this->selectedColorName)
            ->where('master_plan.cancel', 'N')
            ->groupBy('so_det.id', 'so_det.dest', 'so_det.size', 'so_det.color')
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
        $this->emit('toInputPanel', 'defect');
    }

    public function toDefectHistory()
    {
        $this->panels = false;
        $this->defectHistory = !($this->defectHistory);
        $this->emit('toInputPanel', 'defect-history');
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
        $this->emit('fromInputPanel');
        $this->panels = true;
        $this->rft = false;
        $this->defect = false;
        $this->defectHistory = false;
        $this->reject = false;
        $this->rework = false;
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
            case 'defect' :
                // Undo DEFECT
                $defectQuery = OutputFinishing::selectRaw('output_check_finishing.id as defect_id, output_check_finishing.*')->
                    leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
                    leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    where('status', 'defect');
                    if ($this->undoDefectType) $defectQuery->where('output_check_finishing.defect_type_id', $this->undoDefectType);
                    if ($this->undoDefectArea) $defectQuery->where('output_check_finishing.defect_area_id', $this->undoDefectArea);
                    $defectQuery->orderBy('output_check_finishing.updated_at', 'DESC')->
                    orderBy('output_check_finishing.created_at', 'DESC')->
                    take($this->undoQty);

                $getDefects = $defectQuery->get();

                foreach ($getDefects as $getDefect) {
                    $addUndoHistory = UndoFinishing::create([
                        'master_plan_id' => $getDefect->master_plan_id,
                        'so_det_id' => $getDefect->so_det_id,
                        'output_id' => $getDefect->defect_id,
                        'output_type' => $getDefect->status,
                        'keterangan' => 'defect',
                        'undo_by' => Auth::user()->id
                    ]);

                    $deleteDefect = OutputFinishing::find($getDefect->id)->delete();
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
                $getRejects = OutputFinishing::where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    where('status', 'rejected')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    limit($this->undoQty)->
                    get();

                foreach ($getRejects as $reject) {
                    $reject->status = 'defect';
                    $reject->out_at = null;
                    $reject->save();

                    $addUndoHistory = UndoFinishing::create([
                        'master_plan_id' => $reject->master_plan_id,
                        'so_det_id' => $reject->so_det_id,
                        'output_id' => $reject->id,
                        'output_type' => $reject->status,
                        'keterangan' => 'reject',
                        'undo_by' => Auth::user()->id
                    ]);
                }

                if ($getRejects->count() > 0) {
                    $this->emit('alert', 'success', 'Output REJECT dengan ukuran '.$size[0]->size.' berhasil di UNDO sebanyak '.$getRejects->count().' kali.');

                    $this->emit('hideModal', 'undo');
                } else {
                    $this->emit('alert', 'error', 'Output REJECT dengan ukuran '.$size[0]->size.' gagal di UNDO.');
                }

                break;
            case 'rework' :
                // Undo REWORK
                $defectQuery = OutputFinishing::selectRaw('output_check_finishing.id as defect_id, output_check_finishing.*')->
                    leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
                    leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('so_det_id', $this->undoSize)->
                    where('status', 'reworked');
                    if ($this->undoDefectType) $defectQuery->where('output_check_finishing.defect_type_id', $this->undoDefectType);
                    if ($this->undoDefectArea) $defectQuery->where('output_check_finishing.defect_area_id', $this->undoDefectArea);

                $getDefects = $defectQuery->orderBy('output_check_finishing.updated_at', 'DESC')->
                    orderBy('output_check_finishing.created_at', 'DESC')->
                    limit($this->undoQty)->
                    get();

                // update defect & delete rework
                foreach ($getDefects as $defect) {
                    $defect->status = 'defect';
                    $defect->out_at = null;
                    $defect->save();

                    UndoFinishing::create([
                        'master_plan_id' => $defect->master_plan_id,
                        'so_det_id' => $defect->so_det_id,
                        'output_id' => $defect->defect_id,
                        'output_type' => $defect->status,
                        'keterangan' => 'rework',
                        'undo_by' => Auth::user()->id
                    ]);
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
                act_costing.kpno as ws_number,
                act_costing.styleno as style_name,
                mastersupplier.supplier as buyer_name,
                so_det.styleno_prod as reff_number,
                master_plan.color as color,
                so_det.size as size,
                so.qty as qty_order,
                master_plan.sewing_line as sewing_line,
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
            ->where('so_det.cancel', '!=', 'Y')
            ->where('master_plan.cancel', '!=', 'Y')
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

        $this->orderWsDetailSizes = DB::table('master_plan')->selectRaw("
                MIN(so_det.id) as so_det_id,
                so_det.size as size,
                so_det.dest as dest,
                CONCAT(so_det.size, (CASE WHEN so_det.dest != '-' OR so_det.dest IS NULL THEN CONCAT('-', so_det.dest) ELSE '' END)) as size_dest
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->where('master_plan.sewing_line', str_replace(" ", "_", $this->orderInfo->sewing_line))
            ->where('act_costing.kpno', $this->orderInfo->ws_number)
            ->where('so_det.color', $this->selectedColorName)
            ->groupBy('so_det.id', 'so_det.dest', 'so_det.size', 'so_det.color')
            ->orderBy('so_det_id')
            ->get();

        $this->orderDate = $this->orderInfo->tgl_plan;

        session()->put("orderInfo", $this->orderInfo);
        session()->put("orderWsDetails", $this->orderWsDetails);
        session()->put("orderWsDetailSizes", $this->orderWsDetailSizes);

        if ($this->rft) {
            $this->emit('updateWsDetailSizes', 'rft');
        } else if ($this->defect) {
            $this->emit('updateWsDetailSizes', 'defect');
        } else if ($this->rework) {
            $this->emit('updateWsDetailSizes', 'rework');
        } else if ($this->reject) {
            $this->emit('updateWsDetailSizes', 'reject');
        } else {
            $this->emit('updateWsDetailSizes', 'panel');
        }
    }

    public function deleteRedundant() {
        $redundantData = DB::select(DB::raw(
            "select defect_id, jml from (select defect_id, COUNT(defect_id) jml from (SELECT a.* from output_reworks a inner join output_defects c on c.id = a.defect_id inner join master_plan b on b.id = c.master_plan_id where b.sewing_line = '".(Auth::user()->Groupp == 'SEWING' ? "b.sewing_line = '".strtoupper(Auth::user()->username)."' and " : "")."' and DATE_FORMAT(a.created_at, '%Y-%m-%d') = CURRENT_DATE() order by a.defect_id asc) a GROUP BY a.defect_id) a where a.jml > 1"
        ));

        foreach ($redundantData as $redundant) {
            $reworkData = Rework::where('defect_id', $redundant->defect_id)->limit(1)->first();
            Rework::where('id', $reworkData->id)->limit(1)->delete();
            RftModel::where('rework_id', $reworkData->id)->limit(1)->delete();
        }

        $this->emit('alert', 'success', 'Redundant deleted');
    }

    public function setAndSubmitInput($type) {
        $this->emit('loadingStart');

        if ($this->scannedNumberingCode) {
            if (str_contains($this->scannedNumberingCode, 'WIP')) {
                $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $this->scannedNumberingCode)->first();
            } else {
                $numberingCodes = explode('_', $this->scannedNumberingCode);

                if (count($numberingCodes) > 2) {
                    $this->scannedNumberingCode = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                    $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $this->scannedNumberingCode)->first();
                } else {
                    $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $this->scannedNumberingCode)->first();
                }
            }

            if ($numberingData) {
                $this->scannedSizeInput = $numberingData->so_det_id;
                $this->scannedSizeInputText = $numberingData->size;
                $this->scannedNumberingInput = $numberingData->no_cut_size;
            }
        }

        if ($type == "rft") {
            $this->toRft();
            $this->emit('setAndSubmitInputRft', $this->scannedNumberingInput, $this->scannedSizeInput, $this->scannedSizeInputText, $this->scannedNumberingCode);
        }

        if ($type == "defect") {
            $this->toDefect();
            $this->emit('setAndSubmitInputDefect', $this->scannedNumberingInput, $this->scannedSizeInput, $this->scannedSizeInputText, $this->scannedNumberingCode);
        }

        if ($type == "reject") {
            $this->toReject();
            $this->emit('setAndSubmitInputReject', $this->scannedNumberingInput, $this->scannedSizeInput, $this->scannedSizeInputText, $this->scannedNumberingCode);
        }

        if ($type == "rework") {
            $this->toRework();
            $this->emit('setAndSubmitInputRework', $this->scannedNumberingInput, $this->scannedSizeInput, $this->scannedSizeInputText, $this->scannedNumberingCode);
        }
    }

    public function render(SessionManager $session)
    {
        // Keep this data with session
        $this->orderInfo = $session->get("orderInfo", $this->orderInfo);
        $this->orderWsDetails = $session->get("orderWsDetails", $this->orderWsDetails);
        $this->orderWsDetailSizes = $session->get("orderWsDetailSizes", $this->orderWsDetailSizes);

        $this->orderDate = $this->orderInfo->tgl_plan;

        // Get total output
        $this->outputDefect = OutputFinishing::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'defect')->
            count();
        $this->outputReject = OutputFinishing::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'rejected')->
            count();
        $this->outputRework = OutputFinishing::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'reworked')->
            count();

        // Undo size data
        switch ($this->undoType) {
            case 'defect' :
                $this->undoSizes = OutputFinishing::selectRaw('so_det.id as so_det_id, so_det.size, count(*) as total')->
                    leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('status', 'defect')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    groupBy('so_det.id', 'so_det.size')->
                    get();
                break;
            case 'reject' :
                $this->undoSizes = OutputFinishing::selectRaw('so_det.id as so_det_id, so_det.size, count(*) as total')->
                    leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('status', 'rejected')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    groupBy('so_det.id', 'so_det.size')->
                    get();
                break;
            case 'rework' :
                $this->undoSizes = OutputFinishing::selectRaw('so_det.id as so_det_id, so_det.size, count(*) as total')->
                    leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
                    where('master_plan_id', $this->orderInfo->id)->
                    where('status', 'reworked')->
                    orderBy('updated_at', 'DESC')->
                    orderBy('created_at', 'DESC')->
                    groupBy('so_det.id', 'so_det.size')->
                    get();
                break;
        }

        // Defect
        $undoDefectTypes = DefectType::all();
        $undoDefectAreas = DefectArea::all();

        return view('livewire.scan.production-panel', ['undoDefectTypes' => $undoDefectTypes, 'undoDefectAreas' => $undoDefectAreas]);
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }
}
