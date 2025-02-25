<?php

namespace App\Http\Livewire\Scan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\Nds\Numbering;
use App\Models\SignalBit\OutputFinishing;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework as ReworkModel;
use Carbon\Carbon;
use DB;

class Rework extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // filters
    public $orderInfo;
    public $orderWsDetailSizes;
    public $searchDefect;
    public $searchRework;

    // defect position
    public $defectImage;
    public $defectPositionX;
    public $defectPositionY;

    // defect list
    public $allDefectListFilter;
    public $allDefectImage;
    public $allDefectPosition;
    // public $allDefectList;

    // mass rework
    public $massQty;
    public $massSize;
    public $massDefectType;
    public $massDefectTypeName;
    public $massDefectArea;
    public $massDefectAreaName;
    public $massSelectedDefect;

    public $info;

    public $output;
    public $rework;
    public $sizeInputText;
    public $noCutInput;
    public $numberingInput;

    public $rapidRework;
    public $rapidReworkCount;

    protected $rules = [
        'sizeInput' => 'required',
        'noCutInput' => 'required',
        'numberingInput' => 'required',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'noCutInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.',
    ];

    protected $listeners = [
        'submitRework' => 'submitRework',
        'submitAllRework' => 'submitAllRework',
        'cancelRework' => 'cancelRework',
        'hideDefectAreaImageClear' => 'hideDefectAreaImage',
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'setAndSubmitInputRework' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function resetError() {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function updateWsDetailSizes($panel)
    {
        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;

        if ($panel == 'rework') {
            $this->emit('qrInputFocus', 'rework');
        }
    }

    public function updateOutput()
    {
        $this->output = OutputFinishing::where('master_plan_id', $this->orderInfo->id)->
            where('status', 'reworked')->
            count();

        $this->rework = OutputFinishing::where('master_plan_id', $this->orderInfo->id)->
            where('status', 'reworked')->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();
    }

    public function loadReworkPage()
    {
        $this->emit('loadReworkPageJs');
    }

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);

        $this->massSize = '';

        $this->info = true;

        $this->output = 0;
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;

        $this->rapidRework = [];
        $this->rapidReworkCount = 0;
    }

    public function closeInfo()
    {
        $this->info = false;
    }

    public function setDefectAreaPosition($x, $y)
    {
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;
    }

    public function showDefectAreaImage($defectImage, $x, $y)
    {
        $this->defectImage = $defectImage;
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;

        $this->emit('showDefectAreaImage', $this->defectImage, $this->defectPositionX, $this->defectPositionY);
    }

    public function hideDefectAreaImage()
    {
        $this->defectImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    public function updatingSearchDefect()
    {
        $this->resetPage('defectsPage');
    }

    public function updatingSearchRework()
    {
        $this->resetPage('reworksPage');
    }

    public function submitAllRework() {
        $allDefect = DB::connection('mysql_sb')->table('output_check_finishing')->selectRaw('output_check_finishing.id id, output_check_finishing.master_plan_id master_plan_id, output_check_finishing.kode_numbering, output_check_finishing.so_det_id so_det_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->get();

        if ($allDefect->count() > 0) {
            // update defect
            $updateDefect = OutputFinishing::where('master_plan_id', $this->orderInfo->id)->
                where('status', 'defect')->
                update([
                    "status" => "reworked",
                    "out_at" => Carbon::now()
                ]);

            if ($allDefect->count() > 0) {
                $this->emit('alert', 'success', "Semua DEFECT berhasil di REWORK");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }

    public function preSubmitMassRework($defectType, $defectArea, $defectTypeName, $defectAreaName) {
        $this->massQty = 1;
        $this->massSize = '';
        $this->massDefectType = $defectType;
        $this->massDefectTypeName = $defectTypeName;
        $this->massDefectArea = $defectArea;
        $this->massDefectAreaName = $defectAreaName;

        $this->emit('showModal', 'massRework');
    }

    public function submitMassRework() {
        $selectedDefect = OutputFinishing::selectRaw('output_check_finishing.*, so_det.size as size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.defect_type_id', $this->massDefectType)->
            where('output_check_finishing.defect_area_id', $this->massDefectArea)->
            where('output_check_finishing.so_det_id', $this->massSize)->
            take($this->massQty)->get();

        if ($selectedDefect->count() > 0) {
            foreach ($selectedDefect as $defect) {
                // update defect
                $defectSql = OutputFinishing::where('id', $defect->id)->update([
                    "status" => "reworked",
                    "out_at" => Carbon::now()
                ]);
            }

            if ($selectedDefect->count() > 0) {
                $this->emit('alert', 'success', "DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." berhasil di REWORK sebanyak ".$selectedDefect->count()." kali.");

                $this->emit('hideModal', 'massRework');
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }

    public function submitRework($defectId) {
        $thisDefectRework = DB::connection('mysql_sb')->table('output_check_finishing')->where("status", "reworked")->where('id', $defectId)->count();

        if ($thisDefectRework < 1) {
            // remove from defect
            $defect = OutputFinishing::where("status", "defect")->where('id', $defectId)->first();
            $defect->status = "reworked";
            $defect->out_at = Carbon::now();
            $defect->save();

            if ($defect) {
                $this->emit('alert', 'success', "DEFECT dengan ID : ".$defectId." berhasil di REWORK.");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan ID : ".$defectId." tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'warning', "Pencegahan data redundant. DEFECT dengan ID : ".$defectId." sudah ada di REWORK.");
        }
    }

    public function cancelRework($reworkId) {
        // add to defect
        $defect = OutputFinishing::where('id', $reworkId)->first();
        $defect->status = 'defect';
        $defect->out_at = null;
        $defect->save();

        if ($defect) {
            $this->emit('alert', 'success', "REWORK dengan REWORK ID : ".$reworkId." dan DEFECT ID : ".$reworkId." berhasil di kembalikan ke DEFECT.");
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. REWORK dengan REWORK ID : ".$reworkId." dan DEFECT ID : ".$reworkId." tidak berhasil dikembalikan ke DEFECT.");
        }
    }

    public function submitInput()
    {
        $this->emit('renderQrScanner', 'rework');

        if ($this->numberingInput) {
            if (str_contains($this->numberingInput, 'WIP')) {
                $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $this->numberingInput)->first();
            } else {
                $numberingCodes = explode('_', $this->numberingInput);

                if (count($numberingCodes) > 2) {
                    $this->numberingInput = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                    $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $this->numberingInput)->first();
                } else {
                    $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $this->numberingInput)->first();
                }
            }

            if ($numberingData) {
                $this->sizeInput = $numberingData->so_det_id;
                $this->sizeInputText = $numberingData->size;
                $this->noCutInput = $numberingData->no_cut_size;
            }
        }

        $validatedData = $this->validate();

        $scannedDefectData = OutputFinishing::where("status", "defect")->where("kode_numbering", $this->numberingInput)->first();

        if ($scannedDefectData && $this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->count() > 0) {
            // remove from defect
            $scannedDefectData->status = "reworked";
            $scannedDefectData->out_at = Carbon::now();
            $scannedDefectData->save();

            $this->sizeInput = '';
            $this->sizeInputText = '';
            $this->noCutInput = '';
            $this->numberingInput = '';

            if ($scannedDefectData) {
                $this->emit('alert', 'success', "DEFECT dengan ID : ".$scannedDefectData->id." berhasil di REWORK.");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan ID : ".$scannedDefectData->id." tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
        }
    }

    public function setAndSubmitInput($scannedNumbering, $scannedSize, $scannedSizeText) {
        $this->numberingInput = $scannedNumbering;
        $this->sizeInput = $scannedSize;
        $this->sizeInputText = $scannedSizeText;

        $this->submitInput();
    }

    public function pushRapidRework($numberingInput, $sizeInput, $sizeInputText) {
        $exist = false;

        foreach ($this->rapidRework as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput)) {
                $exist = true;
            }
        }

        if (!$exist) {
            $this->rapidReworkCount += 1;

            if ($numberingInput) {
                array_push($this->rapidRework, [
                    'numberingInput' => $numberingInput,
                ]);
            }
        }
    }

    public function submitRapidInput() {
        $defectIds = [];
        $rftData = [];
        $rftDataNds = [];
        $success = 0;
        $fail = 0;

        if ($this->rapidRework && count($this->rapidRework) > 0) {
            for ($i = 0; $i < count($this->rapidRework); $i++) {
                $scannedDefectData = OutputFinishing::where("status", "defect")->where("kode_numbering", $this->rapidRework[$i]['numberingInput'])->first();

                if (($scannedDefectData) && ($this->orderWsDetailSizes->where('so_det_id', $scannedDefectData->so_det_id)->count() > 0)) {
                    array_push($defectIds, $scannedDefectData->id);

                    $success += 1;
                } else {
                    $fail += 1;
                }
            }
        }

        // dd($rapidReworkFiltered, $defectIds, $rftData);

        $rapidDefectUpdate = OutputFinishing::whereIn('id', $defectIds)->
            update([
                "status" => "reworked",
                "out_at" => Carbon::now()
            ]);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidRework = [];
        $this->rapidReworkCount = 0;
    }

    public function render(SessionManager $session)
    {
        if (isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Kode qr sudah discan.")) {
            $this->emit('alert', 'warning', "QR sudah discan.");
        } else if ((isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Harap scan qr.")) || (isset($this->errorBag->messages()['sizeInput']) && collect($this->errorBag->messages()['sizeInput'])->contains("Harap scan qr."))) {
            $this->emit('alert', 'error', "Harap scan QR.");
        }

        $this->emit('loadReworkPageJs');

        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        $this->allDefectImage = MasterPlan::select('gambar')->find($this->orderInfo->id);

        $this->allDefectPosition = OutputFinishing::where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            get();

        $allDefectList = OutputFinishing::selectRaw('output_check_finishing.defect_type_id, output_check_finishing.defect_area_id, output_defect_types.defect_type, output_defect_areas.defect_area, count(*) as total')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereRaw("
                (
                    output_defect_types.defect_type LIKE '%".$this->allDefectListFilter."%' OR
                    output_defect_areas.defect_area LIKE '%".$this->allDefectListFilter."%'
                )
            ")->
            groupBy('output_check_finishing.defect_type_id', 'output_check_finishing.defect_area_id', 'output_defect_types.defect_type', 'output_defect_areas.defect_area')->
            orderBy('output_check_finishing.updated_at', 'desc')->
            paginate(5, ['*'], 'allDefectListPage');

        $defects = OutputFinishing::selectRaw('output_check_finishing.*, so_det.size as so_det_size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereRaw("(
                output_check_finishing.id LIKE '%".$this->searchDefect."%' OR
                so_det.size LIKE '%".$this->searchDefect."%' OR
                output_defect_areas.defect_area LIKE '%".$this->searchDefect."%' OR
                output_defect_types.defect_type LIKE '%".$this->searchDefect."%' OR
                output_check_finishing.status LIKE '%".$this->searchDefect."%'
            )")->
            orderBy('output_check_finishing.updated_at', 'desc')->paginate(10, ['*'], 'defectsPage');

        $reworks = OutputFinishing::selectRaw('output_check_finishing.*, so_det.size as so_det_size')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'reworked')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereRaw("(
                output_check_finishing.id LIKE '%".$this->searchRework."%' OR
                so_det.size LIKE '%".$this->searchRework."%' OR
                output_defect_areas.defect_area LIKE '%".$this->searchRework."%' OR
                output_defect_types.defect_type LIKE '%".$this->searchRework."%' OR
                output_check_finishing.status LIKE '%".$this->searchRework."%'
            )")->
            orderBy('output_check_finishing.updated_at', 'desc')->
            paginate(10, ['*'], 'reworksPage');

        $this->massSelectedDefect = OutputFinishing::selectRaw('output_check_finishing.so_det_id, so_det.size as size, count(*) as total')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.defect_type_id', $this->massDefectType)->
            where('output_check_finishing.defect_area_id', $this->massDefectArea)->
            groupBy('output_check_finishing.so_det_id', 'so_det.size')->get();

        $this->output = OutputFinishing::where('master_plan_id', $this->orderInfo->id)->
            where('status', 'reworked')->
            count();

        $this->rework = OutputFinishing::selectRaw("output_check_finishing.*, so_det.size")->
            leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'reworked')->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();

        return view('livewire.scan.rework' , ['defects' => $defects, 'reworks' => $reworks, 'allDefectList' => $allDefectList]);
    }
}
