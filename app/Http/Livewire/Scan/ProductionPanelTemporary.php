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
use App\Models\SignalBit\EndlineOutput;
use App\Models\SignalBit\TemporaryOutput;
use App\Models\Nds\Numbering;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class ProductionPanelTemporary extends Component
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

    public function mount(SessionManager $session)
    {
        // Default value
        $this->orderDate = date('Y-m-d');
        $this->panels = true;
        $this->rft = false;
        $this->defect = false;
        $this->defectHistory = false;
        $this->reject = false;
        $this->rework = false;

        $this->orderWsDetailSizes = DB::table('master_plan')->selectRaw("
                master_plan.id as master_plan_id,
                MIN(act_costing.kpno) as ws,
                so_det.color as color,
                so_det.id as so_det_id,
                so_det.size as size,
                so_det.dest as dest,
                CONCAT(so_det.size, (CASE WHEN so_det.dest != '-' OR so_det.dest IS NULL THEN CONCAT('-', so_det.dest) ELSE '' END)) as size_dest
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', function($join) {
                $join->on('so_det.id_so', '=', 'so.id');
                $join->on('so_det.color', '=', 'master_plan.color');
            })
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->where('so_det.cancel', '!=', 'Y')
            ->where('master_plan.cancel', '!=', 'Y')
            ->where('master_plan.tgl_plan', $this->orderDate)
            ->where('master_plan.sewing_line', Auth::user()->username)
            ->groupBy('master_plan.id', 'so_det.color', 'so_det.size', 'so_det.dest', 'so_det.id')
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
        }

        if ($type == "rft") {
            $this->toRft();
            $this->emit('setAndSubmitInputRft', $this->scannedNumberingCode);
        }

        if ($type == "defect") {
            $this->toDefect();
            $this->emit('setAndSubmitInputDefect', $this->scannedNumberingInput);
        }

        if ($type == "reject") {
            $this->toReject();
            $this->emit('setAndSubmitInputReject', $this->scannedNumberingInput);
        }

        if ($type == "rework") {
            $this->toRework();
            $this->emit('setAndSubmitInputRework', $this->scannedNumberingInput);
        }
    }

    public function uploadToMasterPlan() {
        $rapidRft = [];
        $rapidDefect = [];
        $rapidRework = [];
        $rapidReject = [];

        $temporaryOutputIds = [];

        $success = 0;
        $fail = 0;

        $temporaryOutputs = DB::connection('mysql_sb')->table('temporary_output_packing')->where("line_id", Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            get();

        foreach ($temporaryOutputs as $tmpOutput) {
            $endlineOutputData = true;
            $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $tmpOutput->so_det_id)->first();
            \Log::info($thisOrderWsDetailSize);
            if (((DB::connection('mysql_sb')->table('output_rfts_packing')->where('kode_numbering', $tmpOutput->kode_numbering)->count() + DB::connection('mysql_sb')->table('output_defects_packing')->where('kode_numbering', $tmpOutput->kode_numbering)->count() + DB::connection('mysql_sb')->table('output_rejects_packing')->where('kode_numbering', $tmpOutput->kode_numbering)->count()) < 1) && ($thisOrderWsDetailSize) && ($endlineOutputData)) {
                array_push($temporaryOutputIds, $tmpOutput->id);

                switch ($tmpOutput->tipe_output) {
                    case "rft" :
                        array_push($rapidRft, [
                            'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                            'so_det_id' => $tmpOutput->so_det_id,
                            'no_cut_size' => $tmpOutput->no_cut_size,
                            'kode_numbering' => $tmpOutput->kode_numbering,
                            'status' => 'NORMAL',
                            'rework_id' => '',
                            'created_by' => Auth::user()->username,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                        break;
                    case "defect":
                        array_push($rapidDefect, [
                            'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                            'so_det_id' => $tmpOutput->sizeInput,
                            'no_cut_size' => $tmpOutput->noCutInput,
                            'kode_numbering' => $tmpOutput->numberingInput,
                            'defect_type_id' => $tmpOutput->defect_type_id,
                            'defect_area_id' => $tmpOutput->defect_area_id,
                            'defect_area_x' => $tmpOutput->defect_area_x,
                            'defect_area_y' => $tmpOutput->defect_area_y,
                            'defect_status' => 'defect',
                            'status' => 'NORMAL',
                            'created_by' => Auth::user()->username,
                            'created_at' => $tmpOutput->created_at,
                            'updated_at' => Carbon::now()
                        ]);
                        break;
                    case "rework":
                        $defect = Defect::create([
                            'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                            'so_det_id' => $tmpOutput->so_det_id,
                            'no_cut_size' => $tmpOutput->no_cut_size,
                            'kode_numbering' => $tmpOutput->kode_numbering,
                            'defect_type_id' => $tmpOutput->defect_type_id,
                            'defect_area_id' => $tmpOutput->defect_area_id,
                            'defect_area_x' => $tmpOutput->defect_area_x,
                            'defect_area_y' => $tmpOutput->defect_area_y,
                            'defect_status' => 'reworked',
                            'status' => 'NORMAL',
                            'created_by' => Auth::user()->username,
                            'created_at' => $tmpOutput->created_at
                        ]);

                        $rework = Rework::create([
                            'defect_id' => $defect->id,
                            'status' => 'NORMAL',
                            'created_by' => Auth::user()->username,
                            'created_at' => $tmpOutput->created_at
                        ]);

                        array_push($rapidRft, [
                            'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                            'so_det_id' => $tmpOutput->so_det_id,
                            'no_cut_size' => $tmpOutput->no_cut_size,
                            'kode_numbering' => $tmpOutput->kode_numbering,
                            'status' => 'REWORK',
                            'rework_id' => $rework->id,
                            'created_by' => Auth::user()->username,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                        break;
                    case "reject":
                        array_push($rapidReject, [
                            'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                            'so_det_id' => $tmpOutput->so_det_id,
                            'no_cut_size' => $tmpOutput->no_cut_size,
                            'kode_numbering' => $tmpOutput->kode_numbering,
                            'status' => 'NORMAL',
                            'created_by' => Auth::user()->username,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                        break;
                    default :
                        dd($tmpOutput);
                        break;
                }

                $success += 1;
            } else {
                $fail += 1;
            }
        }

        $insertRft = Rft::insert($rapidRft);
        $insertDefect = Defect::insert($rapidDefect);
        $insertRework = Rework::insert($rapidRework);
        $insertReject = Reject::insert($rapidReject);

        $deleteUploadedTemporary = TemporaryOutput::whereIn("id", $temporaryOutputIds)->delete();

        if ($success > 0) {
            $this->emit('alert', 'success', $success.' output berhasil di pindahkan.');
        }

        if ($fail > 0) {
            $this->emit('alert', 'error', $fail.' output gagal di pindahkan.');
        }
    }

    public function render(SessionManager $session)
    {
        // Keep this data with session
        $this->orderWsDetailSizes = $session->get("orderWsDetailSizes", $this->orderWsDetailSizes);

        $this->outputRft = DB::connection('mysql_sb')->table('temporary_output_packing')->
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'rft')->
            count();
        $this->outputDefect = DB::connection('mysql_sb')->table('temporary_output_packing')->
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'defect')->
            count();
        $this->outputReject = DB::connection('mysql_sb')->table('temporary_output_packing')->
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'reject')->
            count();
        $this->outputRework = DB::connection('mysql_sb')->table('temporary_output_packing')->
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'rework')->
            count();

        return view('livewire.production-panel-temporary');
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }
}
