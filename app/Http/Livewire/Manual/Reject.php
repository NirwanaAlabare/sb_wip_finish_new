<?php

namespace App\Http\Livewire\Manual;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Session\SessionManager;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\OutputFinishing;
use Carbon\Carbon;
use DB;

class Reject extends Component
{
    public $orderInfo;
    public $orderWsDetailSizes;
    public $output;
    public $outputInput;
    public $sizeInput;
    public $sizeInputText;

    public $searchDefect;
    public $searchReject;
    public $defectImage;
    public $defectPositionX;
    public $defectPositionY;
    public $allDefectListFilter;
    public $allDefectImage;
    public $allDefectPosition;
    public $massQty;
    public $massSize;
    public $massDefectType;
    public $massDefectTypeName;
    public $massDefectArea;
    public $massDefectAreaName;
    public $massSelectedDefect;
    public $info;

    public $defectTypes;
    public $defectAreas;
    public $rejectType;
    public $rejectArea;
    public $rejectAreaPositionX;
    public $rejectAreaPositionY;

    protected $rules = [
        'outputInput' => 'required|numeric|min:1',
        'sizeInput' => 'required',

        'rejectType' => 'required',
        'rejectArea' => 'required',
        'rejectAreaPositionX' => 'required',
        'rejectAreaPositionY' => 'required',
    ];

    protected $messages = [
        'outputInput.required' => 'Harap tentukan kuantitas output.',
        'outputInput.numeric' => 'Harap isi kuantitas output dengan angka.',
        'outputInput.min' => 'Kuantitas output tidak bisa kurang dari 1.',
        'sizeInput.required' => 'Harap tentukan ukuran output.',

        'rejectType.required' => 'Harap tentukan jenis reject.',
        'rejectArea.required' => 'Harap tentukan area reject.',
        'rejectAreaPositionX.required' => "Harap tentukan posisi reject area dengan mengklik tombol 'gambar' di samping 'select product type'.",
        'rejectAreaPositionY.required' => "Harap tentukan posisi reject area dengan mengklik tombol 'gambar' di samping 'select product type'.",
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'updateOutputReject' => 'updateOutput',

        'submitReject' => 'submitReject',
        'submitAllReject' => 'submitAllReject',
        'cancelReject' => 'cancelReject',
        'hideDefectAreaImageClear' => 'hideDefectAreaImage',
        'updateWsDetailSizes' => 'updateWsDetailSizes',

        'setRejectAreaPosition' => 'setRejectAreaPosition',
        'clearInput' => 'clearInput',
    ];

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = null;

        $this->rejectType = null;
        $this->rejectArea = null;
        $this->rejectAreaPositionX = null;
        $this->rejectAreaPositionY = null;
    }

    public function loadRejectPage()
    {
        $this->emit('loadRejectPageJs');
    }

    public function updateWsDetailSizes()
    {
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = '';

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);
    }

    public function updateOutput()
    {
        $this->output = OutputFinishing::
            leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
            where('master_plan_id', $this->orderInfo->id)->
            where('status', "rejected")->
            get();
    }

    public function clearInput()
    {
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = '';
    }

    public function outputIncrement()
    {
        $this->outputInput++;
    }

    public function outputDecrement()
    {
        if (($this->outputInput-1) < 1) {
            $this->emit('alert', 'warning', "Kuantitas output tidak bisa kurang dari 1.");
        } else {
            $this->outputInput--;
        }
    }

    public function setSizeInput($size, $sizeText)
    {
        $this->sizeInput = $size;
        $this->sizeInputText = $sizeText;
    }

    public function selectRejectAreaPosition()
    {
        $masterPlan = MasterPlan::select('gambar')->find($this->orderInfo->id);

        if ($masterPlan) {
            $this->emit('showSelectRejectArea', $masterPlan->gambar);
        } else {
            $this->emit('alert', 'error', 'Harap pilih tipe produk terlebih dahulu');
        }
    }

    public function setRejectAreaPosition($x, $y)
    {
        $this->rejectAreaPositionX = $x;
        $this->rejectAreaPositionY = $y;
    }

    public function preSubmitInput()
    {
        $this->emit('clearSelectRejectAreaPoint');

        $this->rejectType = null;
        $this->rejectArea = null;
        $this->rejectAreaPositionX = null;
        $this->rejectAreaPositionY = null;

        $this->validateOnly('outputInput');
        $this->validateOnly('sizeInput');

        $this->emit('showModal', 'reject');
    }

    public function submitInput(SessionManager $session)
    {
        $validatedData = $this->validate();

        $insertData = [];
        for ($i = 0; $i < $this->outputInput; $i++)
        {
            array_push($insertData, [
                'master_plan_id' => $this->orderInfo->id,
                'so_det_id' => $this->sizeInput,
                'status' => 'rejected',
                'defect_type_id' => $this->rejectType,
                'defect_area_id' => $this->rejectArea,
                'defect_area_x' => $this->rejectAreaPositionX,
                'defect_area_y' => $this->rejectAreaPositionY,
                'created_by' => Auth::user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'out_at' => Carbon::now()
            ]);
        }

        $insertReject = OutputFinishing::insert($insertData);

        if ($insertReject) {
            $type = DefectType::select('defect_type')->find($this->rejectType);
            $area = DefectArea::select('defect_area')->find($this->rejectArea);
            $getSize = DB::table('so_det')
                ->select('id', 'size')
                ->where('id', $this->sizeInput)
                ->first();

            $this->emit('alert', 'success', $this->outputInput." REJECT output berukuran ".$getSize->size." dengan jenis : ".$type->defect_type." dan area : ".$area->defect_area." berhasil terekam.");
            $this->emit('hideModal', 'reject');

            $this->outputInput = 1;
            $this->sizeInput = '';
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
        }
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

    public function updatingSearchReject()
    {
        $this->resetPage('rejectsPage');
    }

    public function submitAllReject() {
        $availableReject = 0;
        $externalReject = 0;

        $allDefect = OutputFinishing::selectRaw('output_check_finishing.id id, output_check_finishing.master_plan_id master_plan_id, output_check_finishing.so_det_id so_det_id, output_check_finishing.kode_numbering, output_defect_types.allocation')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereNull('output_check_finishing.kode_numbering')->
            get();

        if ($allDefect->count() > 0) {
            $defectIds = [];
            foreach ($allDefect as $defect) {
                // add defect ids
                array_push($defectIds, $defect->id);

                $availableReject += 1;
            }
            // update defect
            $defectSql = OutputFinishing::whereIn('id', $defectIds)->update([
                "status" => "rejected",
                "out_at" => Carbon::now(),
            ]);

            if ($availableReject > 0) {
                $this->emit('alert', 'success', $availableReject." DEFECT berhasil di REJECT");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT tidak berhasil di REJECT.");
            }

            if ($externalReject > 0) {
                $this->emit('alert', 'warning', $externalReject." DEFECT masih di proses MANDING/SPOTCLEANING.");
            }

        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }

    public function preSubmitMassReject($defectType, $defectArea, $defectTypeName, $defectAreaName) {
        $this->massQty = 1;
        $this->massSize = '';
        $this->massDefectType = $defectType;
        $this->massDefectTypeName = $defectTypeName;
        $this->massDefectArea = $defectArea;
        $this->massDefectAreaName = $defectAreaName;

        $this->emit('showModal', 'massReject');
    }

    public function submitMassReject() {
        $availableReject = 0;
        $externalReject = 0;

        $selectedDefect = OutputFinishing::selectRaw('output_check_finishing.id id, output_check_finishing.master_plan_id master_plan_id, output_check_finishing.so_det_id so_det_id, output_check_finishing.kode_numbering, output_defect_types.allocation, so_det.size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.defect_type_id', $this->massDefectType)->
            where('output_check_finishing.defect_area_id', $this->massDefectArea)->
            where('output_check_finishing.so_det_id', $this->massSize)->
            whereNull('output_check_finishing.kode_numbering')->
            take($this->massQty)->get();

        if ($selectedDefect->count() > 0) {
            $defectIds = [];
            foreach ($selectedDefect as $defect) {
                if ($defect->in_out_status != "defect") {

                    // add defect id array
                    array_push($defectIds, $defect->id);

                    $availableReject += 1;
                } else {
                    $externalReject += 1;
                }
            }
            // update defect
            $defectSql = OutputFinishing::whereIn('id', $defectIds)->update([
                "status" => "rejected",
                "out_at" => Carbon::now(),
            ]);

            if ($availableReject > 0) {
                $this->emit('alert', 'success', "DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." berhasil di REJECT sebanyak ".$selectedDefect->count()." kali.");

                $this->emit('hideModal', 'massReject');
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." tidak berhasil di REJECT.");
            }

            if ($externalReject > 0) {
                $this->emit('alert', 'warning', $externalReject." DEFECT masih ada yang di proses MENDING/SPOTCLEANING.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }

    public function submitReject($defectId) {
        $externalReject = 0;

        $thisDefectReject = OutputFinishing::where('id', $defectId)->where("status", "defect")->count();

        if ($thisDefectReject > 0) {
            // remove from defect
            $defect = OutputFinishing::where('id', $defectId)->where("status", "defect");

            $updateDefect = $defect->update([
                "status" => "rejected",
                "out_at" => Carbon::now(),
            ]);

            if ($updateDefect) {
                $this->emit('alert', 'success', "DEFECT dengan ID : ".$defectId." berhasil di REJECT.");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan ID : ".$defectId." tidak berhasil di REJECT.");
            }
        } else {
            $this->emit('alert', 'warning', "Defect tidak ditemukan.");
        }
    }

    public function cancelReject($rejectId) {
        // delete from reject
        $updateDefect = OutputFinishing::where('id', $rejectId)->
            update([
                "status" => "defect",
                "out_at" => DB::raw("null"),
            ]);

        if ($updateDefect) {
            $this->emit('alert', 'success', "REJECT dengan REJECT ID : ".$rejectId." dan berhasil di kembalikan ke DEFECT.");
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. REJECT dengan REJECT ID : ".$rejectId." dan tidak berhasil dikembalikan ke DEFECT.");
        }
    }

    public function render(SessionManager $session)
    {
        $this->emit('loadRejectPageJs');

        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Get total output
        $this->output = OutputFinishing::
            leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
            where('master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.status', 'rejected')->
            get();

        $this->allDefectImage = MasterPlan::select('gambar')->find($this->orderInfo->id);

        $this->allDefectPosition = OutputFinishing::where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            get();

        $allDefectList = OutputFinishing::selectRaw('output_check_finishing.defect_type_id, output_check_finishing.defect_area_id, output_defect_types.defect_type, output_defect_areas.defect_area, count(*) as total')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereNull('output_check_finishing.kode_numbering')->
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
            whereNull('output_check_finishing.kode_numbering')->
            whereRaw("(
                output_check_finishing.id LIKE '%".$this->searchDefect."%' OR
                so_det.size LIKE '%".$this->searchDefect."%' OR
                output_defect_areas.defect_area LIKE '%".$this->searchDefect."%' OR
                output_defect_types.defect_type LIKE '%".$this->searchDefect."%' OR
                output_check_finishing.status LIKE '%".$this->searchDefect."%'
            )")->
            orderBy('output_check_finishing.updated_at', 'desc')->paginate(10, ['*'], 'defectsPage');

        $rejects = OutputFinishing::selectRaw('output_check_finishing.*, so_det.size as so_det_size')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'rejected')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereNull('output_check_finishing.kode_numbering')->
            whereRaw("(
                output_check_finishing.id LIKE '%".$this->searchReject."%' OR
                so_det.size LIKE '%".$this->searchReject."%' OR
                output_defect_areas.defect_area LIKE '%".$this->searchReject."%' OR
                output_defect_types.defect_type LIKE '%".$this->searchReject."%' OR
                output_check_finishing.status LIKE '%".$this->searchReject."%'
            )")->
            orderBy('output_check_finishing.updated_at', 'desc')->paginate(10, ['*'], 'rejectsPage');

        $this->massSelectedDefect = OutputFinishing::selectRaw('output_check_finishing.so_det_id, so_det.size as size, count(*) as total')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.defect_type_id', $this->massDefectType)->
            where('output_check_finishing.defect_area_id', $this->massDefectArea)->
            groupBy('output_check_finishing.so_det_id', 'so_det.size')->get();

        // Defect types
        $this->defectTypes = DefectType::whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('defect_type')->get();

        // Defect areas
        $this->defectAreas = DefectArea::whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('defect_area')->get();

        return view('livewire.manual.reject', ['defects' => $defects, 'rejects' => $rejects, 'allDefectList' => $allDefectList]);
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }
}
