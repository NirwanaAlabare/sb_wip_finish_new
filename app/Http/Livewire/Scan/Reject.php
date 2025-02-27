<?php

namespace App\Http\Livewire\Scan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\OutputFinishing;
use App\Models\Nds\Numbering;
use Carbon\Carbon;
use Validator;
use DB;

class Reject extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $orderInfo;
    public $orderWsDetailSizes;
    public $output;
    public $sizeInput;
    public $sizeInputText;
    public $noCutInput;
    public $numberingInput;
    public $reject;

    public $rapidReject;
    public $rapidRejectCount;

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
        'sizeInput' => 'required',
        'noCutInput' => 'required',
        'numberingInput' => 'required',

        'rejectType' => 'required',
        'rejectArea' => 'required',
        'rejectAreaPositionX' => 'required',
        'rejectAreaPositionY' => 'required',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'noCutInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.',

        'rejectType.required' => 'Harap tentukan jenis reject.',
        'rejectArea.required' => 'Harap tentukan area reject.',
        'rejectAreaPositionX.required' => "Harap tentukan posisi reject area dengan mengklik tombol 'gambar' di samping 'select product type'.",
        'rejectAreaPositionY.required' => "Harap tentukan posisi reject area dengan mengklik tombol 'gambar' di samping 'select product type'.",
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'updateOutputReject' => 'updateOutput',
        'setAndSubmitInputReject' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError',

        'submitInputReject' => 'submitInput',
        'submitReject' => 'submitReject',
        'submitAllReject' => 'submitAllReject',
        'cancelReject' => 'cancelReject',
        'hideDefectAreaImageClear' => 'hideDefectAreaImage',
        'updateWsDetailSizes' => 'updateWsDetailSizes',

        'setRejectAreaPosition' => 'setRejectAreaPosition',
        'clearInput' => 'clearInput'
    ];

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->sizeInput = null;

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;

        $this->rejectType = null;
        $this->rejectArea = null;
        $this->rejectAreaPositionX = null;
        $this->rejectAreaPositionY = null;
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function resetError() {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function loadRejectPage()
    {
        $this->emit('loadRejectPageJs');
    }

    public function updateWsDetailSizes($panel)
    {
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        if ($panel == 'reject') {
            $this->emit('qrInputFocus', 'reject');
        }
    }

    public function updateOutput()
    {
        // Get total output
        $this->output = OutputFinishing::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'rejected')->
            count();

        // Reject
        $this->reject = OutputFinishing::
            selectRaw('output_check_finishing.*, so_det.size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'rejected')->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();
    }

    public function clearInput()
    {
        $this->sizeInput = null;
        $this->noCutInput = null;
        $this->numberingInput = null;
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

        $scannedDefectData = OutputFinishing::selectRaw("output_check_finishing.*, output_check_finishing.master_plan_id, master_plan.sewing_line, output_defect_in_out.status as in_out_status")->
            leftJoin("output_defect_in_out", function ($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_check_finishing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qcf'"));
            })->
            leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
            where("output_check_finishing.kode_numbering", $this->numberingInput)->
            first();

        // check defect
        if ($scannedDefectData) {
            if ($scannedDefectData->status == "defect") {
                $this->rejectType = $scannedDefectData->defect_type_id;
                $this->rejectArea = $scannedDefectData->defect_area_id;
                $this->rejectAreaPositionX = $scannedDefectData->defect_area_x;
                $this->rejectAreaPositionY = $scannedDefectData->defect_area_y;

                $this->emit('loadingStart');

                $this->emitSelf('submitInputReject');
            } else {
                $this->emit('qrInputFocus', 'reject');

                $this->emit('alert', 'warning', "Kode qr sudah discan.");
            }
        } else {
            $validation = Validator::make([
                'sizeInput' => $this->sizeInput,
                'noCutInput' => $this->noCutInput,
                'numberingInput' => $this->numberingInput
            ], [
                'sizeInput' => 'required',
                'noCutInput' => 'required',
                'numberingInput' => 'required'
            ], [
                'sizeInput.required' => 'Harap scan qr.',
                'noCutInput.required' => 'Harap scan qr.',
                'numberingInput.required' => 'Harap scan qr.',
            ]);

            if ($validation->fails()) {
                $this->emit('qrInputFocus', 'reject');

                $validation->validate();
            } else {
                if ($this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->count() > 0) {
                    $this->emit('clearSelectDefectAreaPoint');

                    $this->rejectType = null;
                    $this->rejectArea = null;
                    $this->rejectAreaPositionX = null;
                    $this->rejectAreaPositionY = null;

                    $this->validateOnly('sizeInput');

                    $this->emit('showModal', 'reject', 'regular');
                } else {
                    $this->emit('qrInputFocus', 'reject');
                    dd($this->orderWsDetailSizes->where('so_det_id', $this->sizeInput));

                    $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
                }
            }
        }
    }

    public function submitInput()
    {
        $this->emit('qrInputFocus', 'reject');

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

        if ($this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->count() > 0) {
            $scannedDefectData = OutputFinishing::selectRaw("output_check_finishing.*, output_check_finishing.master_plan_id, master_plan.sewing_line, output_defect_in_out.status as in_out_status")->
                leftJoin("output_defect_in_out", function ($join) {
                    $join->on("output_defect_in_out.defect_id", "=", "output_check_finishing.id");
                    $join->on("output_defect_in_out.output_type", "=", DB::raw("'qcf'"));
                })->
                leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                where("output_check_finishing.kode_numbering", $this->numberingInput)->
                first();

            // check defect
            if ($scannedDefectData) {
                if ($scannedDefectData->status == "defect" && $scannedDefectData->master_plan_id == $this->orderInfo->id) {
                    if ($scannedDefectData->in_out_status != "defect") {
                        $scannedDefectData->status = "rejected";
                        $scannedDefectData->out_at = Carbon::now();
                        $scannedDefectData->save();

                        $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam.");
                        $this->emit('hideModal', 'reject', 'regular');
                    } else {
                        $this->emit('alert', 'error', "DEFECT masih berada di MENDING/SPOTCLEANING.");
                    }
                } else {
                    $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
                }
            } else {
                $insertReject = OutputFinishing::create([
                    'master_plan_id' => $this->orderInfo->id,
                    'so_det_id' => $this->sizeInput,
                    'kode_numbering' => $this->numberingInput,
                    'status' => 'rejected',
                    'defect_type_id' => $this->rejectType,
                    'defect_area_id' => $this->rejectArea,
                    'defect_area_x' => $this->rejectAreaPositionX,
                    'defect_area_y' => $this->rejectAreaPositionY,
                    'created_by' => Auth::user()->username,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'out_at' => Carbon::now(),
                ]);

                if ($insertReject) {
                    $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam.");
                    $this->emit('hideModal', 'reject', 'regular');

                    $this->sizeInput = '';
                    $this->sizeInputText = '';
                    $this->noCutInput = '';
                    $this->numberingInput = '';
                } else {
                    dd($this->orderWsDetailSizes->where('so_det_id', $this->sizeInput));
                    $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
                }
            }
        } else {
            dd($this->orderWsDetailSizes, $this->sizeInput, $this->orderWsDetailSizes->where('so_det_id', $this->sizeInput));
            $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
        }

        $this->emit('qrInputFocus', 'reject');
    }

    public function setAndSubmitInput($scannedNumbering, $scannedSize, $scannedSizeText) {
        $this->numberingInput = $scannedNumbering;
        $this->sizeInput = $scannedSize;
        $this->sizeInputText = $scannedSizeText;

        $this->preSubmitInput();
    }

    public function pushRapidReject($numberingInput, $sizeInput, $sizeInputText) {
        $exist = false;

        if (count($this->rapidReject) < 100) {
            foreach ($this->rapidReject as $item) {
                if (($numberingInput && $item['numberingInput'] == $numberingInput)) {
                    $exist = true;
                }
            }

            if (!$exist) {
                if ($numberingInput) {
                    $this->rapidRejectCount += 1;

                    array_push($this->rapidReject, [
                        'numberingInput' => $numberingInput,
                    ]);
                }
            }
        } else {
            $this->emit('alert', 'error', "Anda sudah mencapai batas rapid scan. Harap klik selesai dahulu.");
        }
    }

    public function preSubmitRapidInput()
    {
        $this->rejectType = null;
        $this->rejectArea = null;
        $this->rejectAreaPositionX = null;
        $this->rejectAreaPositionY = null;

        $this->emit('showModal', 'reject', 'rapid');
    }

    public function submitRapidInput() {
        $rapidRejectFiltered = [];
        $success = 0;
        $fail = 0;

        if ($this->rapidReject && count($this->rapidReject) > 0) {
            for ($i = 0; $i < count($this->rapidReject); $i++) {
                if (str_contains($this->rapidReject[$i]['numberingInput'], 'WIP')) {
                    $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $this->rapidReject[$i]['numberingInput'])->first();
                } else {
                    $numberingCodes = explode('_', $this->rapidReject[$i]['numberingInput']);

                    if (count($numberingCodes) > 1) {
                        $this->rapidReject[$i]['numberingInput'] = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                        $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $this->rapidReject[$i]['numberingInput'])->first();
                    } else {
                        $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $this->rapidReject[$i]['numberingInput'])->first();
                    }
                }

                if (($this->orderWsDetailSizes->where('so_det_id', $numberingData->so_det_id)->count() > 0)) {
                    $scannedDefectData = OutputFinishing::selectRaw("output_check_finishing.*, output_check_finishing.master_plan_id, master_plan.sewing_line, output_defect_in_out.status as in_out_status")->
                        leftJoin("output_defect_in_out", function ($join) {
                            $join->on("output_defect_in_out.defect_id", "=", "output_check_finishing.id");
                            $join->on("output_defect_in_out.output_type", "=", DB::raw("'qcf'"));
                        })->
                        leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                        where("output_check_finishing.status", "defect")->
                        where("output_check_finishing.kode_numbering", $this->numberingInput)->
                        first();

                    if ($scannedDefectData) {
                        if ($scannedDefectData->status == "defect" && $scannedDefectData->master_plan_id == $this->orderInfo->id) {
                            if ($scannedDefectData->in_out_status != "defect") {
                                $scannedDefectData->status = 'rejected';
                                $scannedDefectData->out_at = Carbon::now();
                                $scannedDefectData->save();

                                $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam.");
                                $this->emit('hideModal', 'reject', 'regular');

                                $success += 1;
                            } else {
                                $this->emit('alert', 'error', "DEFECT masih berada di MENDING/SPOTCLEANING.");

                                $fail += 1;
                            }
                        } else {
                            $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");

                            $fail += 1;
                        }
                    } else {
                        array_push($rapidRejectFiltered, [
                            'master_plan_id' => $this->orderInfo->id,
                            'so_det_id' => $numberingData->so_det_id,
                            'kode_numbering' => $this->rapidReject[$i]['numberingInput'],
                            'defect_type_id' => $scannedDefectData ? $scannedDefectData->defectType->defect_type_id : $this->rejectType,
                            'defect_area_id' => $scannedDefectData ? $scannedDefectData->defectArea->defect_area_id : $this->rejectArea,
                            'defect_area_x' => $scannedDefectData ? $scannedDefectData->defect_area_x : $this->rejectAreaPositionX,
                            'defect_area_y' => $scannedDefectData ? $scannedDefectData->defect_area_y : $this->rejectAreaPositionY,
                            'status' => 'rejected',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'out_at' => Carbon::now(),
                            'created_by' => Auth::user()->username
                        ]);

                        $success += 1;
                    }
                } else {
                    $fail += 1;
                }
            }
        }

        $rapidRejectInsert = OutputFinishing::insert($rapidRejectFiltered);

        if ($success > 0) {
            $this->emit('alert', 'success', $success." output berhasil terekam. ");
        }

        if ($fail > 0) {
            $this->emit('alert', 'error', $fail." output gagal terekam.");
        }

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;
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

        $allDefect = OutputFinishing::selectRaw('output_check_finishing.id id, output_check_finishing.master_plan_id master_plan_id, output_check_finishing.so_det_id so_det_id, output_check_finishing.kode_numbering, output_defect_types.allocation, output_defect_in_out.status in_out_status')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            leftJoin("output_defect_in_out", function ($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_check_finishing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qcf'"));
            })->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereNull('output_check_finishing.kode_numbering')->
            get();

        if ($allDefect->count() > 0) {
            $defectIds = [];

            foreach ($allDefect as $defect) {
                if ($defect->in_out_status != "defect") {
                    // add defect ids
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
                $this->emit('alert', 'success', $availableReject." DEFECT berhasil di REJECT");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT tidak berhasil di REJECT.");
            }

            if ($externalReject > 0) {
                $this->emit('alert', 'warning', $externalReject." DEFECT masih di proses MENDING/SPOTCLEANING.");
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

        $selectedDefect = OutputFinishing::selectRaw('output_check_finishing.id id, output_check_finishing.master_plan_id master_plan_id, output_check_finishing.so_det_id so_det_id, output_check_finishing.kode_numbering, output_defect_types.allocation, so_det.size, output_defect_in_out.status in_out_status')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            leftJoin("output_defect_in_out", function ($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_check_finishing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qcf'"));
            })->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.defect_type_id', $this->massDefectType)->
            where('output_check_finishing.defect_area_id', $this->massDefectArea)->
            where('output_check_finishing.so_det_id', $this->massSize)->
            whereNull('output_check_finishing.kode_numbering')->
            take($this->massQty)->
            get();

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
                "out_at" => Carbon::now()
            ]);

            if ($availableReject > 0) {
                $this->emit('alert', 'success', "DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." berhasil di REJECT sebanyak ".$selectedDefect->count()." kali.");

                $this->emit('hideModal', 'massReject');
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." tidak berhasil di REJECT.");
            }

            if ($externalReject > 0) {
                $this->emit('alert', 'warning', $externalReject." DEFECT masih ada yang di proses MANDING/SPOTCLEANING.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }

    public function submitReject($defectId) {
        $externalReject = 0;

        $thisDefectReject = OutputFinishing::where('id', $defectId)->where('rejected')->count();

        if ($thisDefectReject < 1) {
            // remove from defect
            $defect = OutputFinishing::where("status", "defect")->where('id', $defectId);
            $getDefect = OutputFinishing::selectRaw('output_check_finishing.*, output_defect_in_out.status in_out_status')->
                leftJoin("output_defect_in_out", function ($join) {
                    $join->on("output_defect_in_out.defect_id", "=", "output_check_finishing.id");
                    $join->on("output_defect_in_out.output_type", "=", DB::raw("'qcf'"));
                })->
                where("status", "defect")->
                where('output_check_finishing.id', $defectId)->
                first();

            if ($getDefect->in_out_status != 'defect') {
                $updateDefect = $defect->update([
                    "status" => "rejected",
                    "out_at" => Carbon::now()
                ]);

                if ($updateDefect) {
                    $this->emit('alert', 'success', "DEFECT dengan ID : ".$defectId." berhasil di REJECT.");
                } else {
                    $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan ID : ".$defectId." tidak berhasil di REJECT.");
                }
            } else {
                $this->emit('alert', 'error', "DEFECT ini masih di proses MANDING/SPOTCLEANING. DEFECT dengan ID : ".$defectId." tidak berhasil di REJECT.");
            }
        } else {
            $this->emit('alert', 'warning', "Pencegahan data redundant. DEFECT dengan ID : ".$defectId." sudah ada di REJECT.");
        }
    }

    public function cancelReject($rejectId) {
        // add to defect
        $updateDefect = OutputFinishing::where('id', $rejectId)->update([
            "status" => "defect",
            "out_at" => null,
        ]);

        if ($updateDefect) {
            $this->emit('alert', 'success', "REJECT dengan ID : ".$rejectId." berhasil di kembalikan ke DEFECT.");
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. REJECT ID : ".$rejectId." tidak berhasil dikembalikan ke DEFECT.");
        }
    }

    public function render(SessionManager $session)
    {
        $this->emit('loadRejectPageJs');

        if (isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Kode qr sudah discan.")) {
            $this->emit('alert', 'warning', "QR sudah discan.");
        } else if ((isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Harap scan qr.")) || (isset($this->errorBag->messages()['sizeInput']) && collect($this->errorBag->messages()['sizeInput'])->contains("Harap scan qr."))) {
            $this->emit('alert', 'error', "Harap scan QR.");
        }

        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Get total output
        $this->output = DB::connection('mysql_sb')->table('output_check_finishing')->
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'rejected')->
            count();

        // Reject
        $this->reject = DB::connection('mysql_sb')->table('output_check_finishing')->
            selectRaw('output_check_finishing.*, so_det.size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('master_plan_id', $this->orderInfo->id)->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            where('status', 'rejected')->
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
            whereRaw("
                (
                    output_defect_types.defect_type LIKE '%".$this->allDefectListFilter."%' OR
                    output_defect_areas.defect_area LIKE '%".$this->allDefectListFilter."%'
                )
            ")->
            groupBy('output_check_finishing.defect_type_id', 'output_check_finishing.defect_area_id', 'output_defect_types.defect_type', 'output_defect_areas.defect_area')->
            orderBy('output_check_finishing.updated_at', 'desc')->
            paginate(5, ['*'], 'allDefectListPage');

        $defects = OutputFinishing::selectRaw('output_check_finishing.*, output_defect_types.defect_type, output_defect_areas.defect_area, master_plan.gambar, so_det.size as so_det_size')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
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

        $rejects = OutputFinishing::selectRaw('output_check_finishing.*, so_det.size as so_det_size')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.status', 'rejected')->
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

        return view('livewire.scan.reject', ['defects' => $defects, 'rejects' => $rejects, 'allDefectList' => $allDefectList]);
    }
}
