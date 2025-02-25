<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\ProductType;
use App\Models\SignalBit\Defect as DefectModel;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\EndlineOutput;
use App\Models\SignalBit\TemporaryOutput;
use App\Models\Nds\Numbering;
use Carbon\Carbon;
use Validator;
use DB;

class DefectTemporary extends Component
{
    use WithFileUploads;

    public $orderDate;
    public $orderWsDetailSizes;
    public $sizeInput;
    public $sizeInputText;
    public $noCutInput;
    public $numberingInput;
    public $defect;

    public $defectTypes;
    public $defectAreas;
    public $productTypes;

    public $defectType;
    public $defectArea;
    public $productType;

    public $defectTypeAdd;
    public $defectAreaAdd;
    public $productTypeAdd;

    public $productTypeImageAdd;
    public $defectAreaPositionX;
    public $defectAreaPositionY;

    public $rapidDefect;
    public $rapidDefectCount;

    protected $rules = [
        'sizeInput' => 'required',
        'noCutInput' => 'required',
        'numberingInput' => 'required|unique:output_rfts_packing,kode_numbering|unique:output_defects_packing,kode_numbering|unique:output_rejects_packing,kode_numbering|unique:temporary_output_packing,kode_numbering',
        // 'productType' => 'required',
        'defectType' => 'required',
        'defectArea' => 'required',
        'defectAreaPositionX' => 'required',
        'defectAreaPositionY' => 'required',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'noCutInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.',
        'numberingInput.unique' => 'Kode qr sudah discan.',
        // 'productType.required' => 'Harap tentukan tipe produk.',
        'defectType.required' => 'Harap tentukan jenis defect.',
        'defectArea.required' => 'Harap tentukan area defect.',
        'defectAreaPositionX.required' => "Harap tentukan posisi defect area dengan mengklik tombol 'gambar' di samping 'select product type'.",
        'defectAreaPositionY.required' => "Harap tentukan posisi defect area dengan mengklik tombol 'gambar' di samping 'select product type'.",
    ];

    protected $listeners = [
        'setDefectAreaPosition' => 'setDefectAreaPosition',
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'setAndSubmitInputDefect' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function mount(SessionManager $session, $orderDate, $orderWsDetailSizes)
    {
        $this->orderDate = $orderDate;
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;

        $this->defectType = null;
        $this->defectArea = null;
        $this->productType = null;

        $this->defectAreaPositionX = null;
        $this->defectAreaPositionY = null;

        $this->rapidDefect = [];
        $this->rapidDefectCount = 0;
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

    public function updateWsDetailSizes($panel)
    {
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;

        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        if ($panel == 'defect') {
            $this->emit('qrInputFocus', 'defect');
        }
    }

    public function updatedproductTypeImageAdd()
    {
        $this->validate([
            'productTypeImageAdd' => 'image',
        ]);
    }

    public function submitProductType()
    {
        if ($this->productTypeAdd && $this->productTypeImageAdd) {

            $productTypeImageAddName = md5($this->productTypeImageAdd . microtime()).'.'.$this->productTypeImageAdd->extension();
            $this->productTypeImageAdd->storeAs('public/images', $productTypeImageAddName);

            $createProductType = ProductType::create([
                'product_type' => $this->productTypeAdd,
                'image' => $productTypeImageAddName,
            ]);

            if ($createProductType) {
                $this->emit('alert', 'success', 'Product Time : '.$this->productTypeAdd.' berhasil ditambahkan.');

                $this->productTypeAdd = null;
                $this->productTypeImageAdd = null;
            } else {
                $this->emit('alert', 'error', 'Terjadi kesalahan.');
            }
        } else {
            $this->emit('alert', 'error', 'Harap tentukan nama tipe produk beserta gambarnya');
        }
    }

    public function submitDefectType()
    {
        if ($this->defectTypeAdd) {
            $createDefectType = DefectType::create([
                'defect_type' => $this->defectTypeAdd
            ]);

            if ($createDefectType) {
                $this->emit('alert', 'success', 'Defect type : '.$this->defectTypeAdd.' berhasil ditambahkan.');

                $this->defectTypeAdd = '';
            } else {
                $this->emit('alert', 'error', 'Terjadi kesalahan.');
            }
        } else {
            $this->emit('alert', 'error', 'Harap tentukan nama defect type');
        }
    }

    public function submitDefectArea()
    {
        if ($this->defectAreaAdd) {

            $createDefectArea = DefectArea::create([
                'defect_area' => $this->defectAreaAdd,
            ]);

            if ($createDefectArea) {
                $this->emit('alert', 'success', 'Defect area : '.$this->defectAreaAdd.' berhasil ditambahkan.');

                $this->defectAreaAdd = null;
            } else {
                $this->emit('alert', 'error', 'Terjadi kesalahan.');
            }
        } else {
            $this->emit('alert', 'error', 'Harap tentukan nama defect area');
        }
    }

    public function clearInput()
    {
        $this->sizeInput = '';
    }

    public function selectDefectAreaPosition($type = "normal")
    {
        $thisOrderWsDetailSize = $this->orderWsDetailSizes->where("so_det_id", $this->sizeInput)->first();

        if ($thisOrderWsDetailSize) {
            $masterPlan = DB::connection('mysql_sb')->table('master_plan')->select('gambar')->find($thisOrderWsDetailSize['master_plan_id']);

            if ($masterPlan) {
                $this->emit('showSelectDefectArea', $masterPlan->gambar);
            } else {
                $this->emit('alert', 'error', 'Harap pilih tipe produk terlebih dahulu');
            }
        } else {
            $masterPlan = DB::connection('mysql_sb')->table('master_plan')->
                select('gambar')->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("so", "so.id_cost", "=", "act_costing.id")->
                leftJoin("so_det", "so_det.id_so", "=", "so.id")->
                where("so_det.id", $this->sizeInput)->
                orderBy("master_plan.tgl_plan", "desc")->
                first();

            if ($masterPlan) {
                $this->emit('showSelectDefectArea', $masterPlan->gambar);
            } else {
                $this->emit('alert', 'error', 'Harap pilih tipe produk terlebih dahulu');
            }
        }
    }

    public function setDefectAreaPosition($x, $y)
    {
        $this->defectAreaPositionX = $x;
        $this->defectAreaPositionY = $y;
    }

    public function preSubmitInput()
    {
        if ($this->numberingInput) {
            if (str_contains($this->numberingInput, 'WIP')) {
                $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $this->numberingInput)->first();
            } else {
                $numberingCodes = explode('_', $numberingInput);

                if (count($numberingCodes) > 2) {
                    $numberingInput = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                    $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $numberingInput)->first();
                } else {
                    $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $numberingInput)->first();
                }
            }

            if ($numberingData) {
                $this->sizeInput = $numberingData->so_det_id;
                $this->sizeInputText = $numberingData->size;
                $this->noCutInput = $numberingData->no_cut_size;
            }
        }

        $validation = Validator::make([
            'sizeInput' => $this->sizeInput,
            'noCutInput' => $this->noCutInput,
            'numberingInput' => $this->numberingInput
        ], [
            'sizeInput' => 'required',
            'noCutInput' => 'required',
            'numberingInput' => 'required|unique:output_rfts_packing,kode_numbering|unique:output_defects_packing,kode_numbering|unique:output_rejects_packing,kode_numbering|unique:temporary_output_packing,kode_numbering'
        ], [
            'sizeInput.required' => 'Harap scan qr.',
            'noCutInput.required' => 'Harap scan qr.',
            'numberingInput.required' => 'Harap scan qr.',
            'numberingInput.unique' => 'Kode qr sudah discan.',
        ]);

        if ($validation->fails()) {
            $this->emit('qrInputFocus', 'defect');

            $validation->validate();
        } else {
            if ($this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->count() > 0) {
                $this->emit('clearSelectDefectAreaPoint');

                $this->defectType = null;
                $this->defectArea = null;
                $this->productType = null;
                $this->defectAreaPositionX = null;
                $this->defectAreaPositionY = null;

                $this->validateOnly('sizeInput');

                $this->emit('showModal', 'defect', 'regular');
            } else {
                $this->emit('clearSelectDefectAreaPoint');

                $this->defectType = null;
                $this->defectArea = null;
                $this->productType = null;
                $this->defectAreaPositionX = null;
                $this->defectAreaPositionY = null;

                $this->validateOnly('sizeInput');

                $this->emit('showModal', 'defect', 'regular');

                $this->emit('alert', 'warning', "Master Plan tidak ditemukan.");
            }
        }
    }

    public function submitInput(SessionManager $session)
    {
        $validatedData = $this->validate();

        $endlineOutputData = DB::connection('mysql_sb')->table('output_rfts')->where("kode_numbering", $this->numberingInput)->first();
        $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->first();
        if ($endlineOutputData && $thisOrderWsDetailSize) {
            $insertDefect = DefectModel::create([
                'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                'no_cut_size' => $this->noCutInput,
                'kode_numbering' => $this->numberingInput,
                'so_det_id' => $this->sizeInput,
                // 'product_type_id' => $this->productType,
                'defect_type_id' => $this->defectType,
                'defect_area_id' => $this->defectArea,
                'defect_area_x' => $this->defectAreaPositionX,
                'defect_area_y' => $this->defectAreaPositionY,
                'status' => 'NORMAL',
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertDefect) {
                $type = DefectType::select('defect_type')->find($this->defectType);
                $area = DefectArea::select('defect_area')->find($this->defectArea);
                $getSize = DB::table('so_det')
                    ->select('id', 'size')
                    ->where('id', $this->sizeInput)
                    ->first();

                $this->emit('alert', 'success', "1 output DEFECT berukuran ".$getSize->size." dengan jenis defect : ".$type->defect_type." dan area defect : ".$area->defect_area." berhasil terekam.");
                $this->emit('hideModal', 'defect', 'regular');

                $this->sizeInput = '';
                $this->sizeInputText = '';
                $this->noCutInput = '';
                $this->numberingInput = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }

            $this->emit('qrInputFocus', 'defect');
        } else {
            $insertTemporary = TemporaryOutput::create([
                'line_id' => Auth::user()->line_id,
                'no_cut_size' => $this->noCutInput,
                'kode_numbering' => $this->numberingInput,
                'so_det_id' => $this->sizeInput,
                'size' => $this->sizeInputText,
                // 'product_type_id' => $this->productType,
                'defect_type_id' => $this->defectType,
                'defect_area_id' => $this->defectArea,
                'defect_area_x' => $this->defectAreaPositionX,
                'defect_area_y' => $this->defectAreaPositionY,
                'tipe_output' => 'defect',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertTemporary) {
                $type = DefectType::select('defect_type')->find($this->defectType);
                $area = DefectArea::select('defect_area')->find($this->defectArea);
                $getSize = DB::table('so_det')
                    ->select('id', 'size')
                    ->where('id', $this->sizeInput)
                    ->first();

                $this->emit('alert', 'warning', "Tanpa Master Plan.");
                $this->emit('alert', 'success', "1 output DEFECT berukuran ".$getSize->size." dengan jenis defect : ".$type->defect_type." dan area defect : ".$area->defect_area." berhasil terekam di TEMPORARY.");
                $this->emit('hideModal', 'defect', 'regular');

                $this->sizeInput = '';
                $this->sizeInputText = '';
                $this->noCutInput = '';
                $this->numberingInput = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }
        }
    }

    public function setAndSubmitInput($scannedNumbering) {
        $this->numberingInput = $scannedNumbering;

        $this->preSubmitInput();
    }

    public function pushRapidDefect($numberingInput, $sizeInput, $sizeInputText) {
        $exist = false;

        foreach ($this->rapidDefect as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput)) {
                $exist = true;
            } else {
                if (str_contains($numberingInput, 'WIP')) {
                    $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $numberingInput)->first();
                } else {
                    $numberingCodes = explode('_', $numberingInput);

                    if (count($numberingCodes) > 2) {
                        $numberingInput = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                        $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $numberingInput)->first();
                    } else {
                        $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $numberingInput)->first();
                    }
                }

                if ($numberingData) {
                    if ($item['masterPlanId'] && $item['masterPlanId'] != $this->orderWsDetailSizes->where("so_det_id", $numberingData->so_det_id)->first()['master_plan_id']) {
                        $exist = true;
                    }
                }
            }
        }

        if (!$exist) {
            $this->rapidDefectCount += 1;

            if ($numberingInput) {
                if (str_contains($numberingInput, 'WIP')) {
                    $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $numberingInput)->first();
                } else {
                    $numberingCodes = explode('_', $numberingInput);

                    if (count($numberingCodes) > 2) {
                        $numberingInput = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                        $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $numberingInput)->first();
                    } else {
                        $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $numberingInput)->first();
                    }
                }

                if ($numberingData) {
                    $sizeInput = $numberingData->so_det_id;
                    $sizeInputText = $numberingData->size;
                    $noCutInput = $numberingData->no_cut_size;
                    $masterPlanId = $this->orderWsDetailSizes->where("so_det_id", $sizeInput)->first() ? $this->orderWsDetailSizes->where("so_det_id", $sizeInput)->first()['master_plan_id'] : null;

                    array_push($this->rapidDefect, [
                        'numberingInput' => $numberingInput,
                        'sizeInput' => $sizeInput,
                        'sizeInputText' => $sizeInputText,
                        'noCutInput' => $noCutInput,
                        'masterPlanId' => $masterPlanId
                    ]);
                }
            }

            $this->sizeInput = $sizeInput;
        }
    }

    public function preSubmitRapidInput()
    {
        $this->defectType = null;
        $this->defectArea = null;
        $this->productType = null;
        $this->defectAreaPositionX = null;
        $this->defectAreaPositionY = null;

        $this->emit('showModal', 'defect', 'rapid');
    }

    public function submitRapidInput() {
        $rapidDefectFiltered = [];
        $rapidTemporaryFiltered = [];
        $success = 0;
        $temporary = 0;
        $fail = 0;

        if ($this->rapidDefect && count($this->rapidDefect) > 0) {
            for ($i = 0; $i < count($this->rapidDefect); $i++) {
                $endlineOutputData = DB::connection('mysql_sb')->table('output_rfts')->where("kode_numbering", $this->rapidDefect[$i]['numberingInput'])->first();
                $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $this->rapidDefect[$i]['sizeInput'])->first();
                if (((DB::connection('mysql_sb')->table('output_defects_packing')->where('kode_numbering', $this->rapidDefect[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rfts_packing')->where('kode_numbering', $this->rapidDefect[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rejects_packing')->where('kode_numbering', $this->rapidDefect[$i]['numberingInput'])->count()) < 1) && ($thisOrderWsDetailSize) && ($endlineOutputData)) {
                    array_push($rapidDefectFiltered, [
                        'master_plan_id' => $thisOrderWsDetailSize["master_plan_id"],
                        'so_det_id' => $this->rapidDefect[$i]['sizeInput'],
                        'no_cut_size' => $this->rapidDefect[$i]['noCutInput'],
                        'kode_numbering' => $this->rapidDefect[$i]['numberingInput'],
                        'defect_type_id' => $this->defectType,
                        'defect_area_id' => $this->defectArea,
                        'defect_area_x' => $this->defectAreaPositionX,
                        'defect_area_y' => $this->defectAreaPositionY,
                        'status' => 'NORMAL',
                        'created_by' => Auth::user()->username,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    $success += 1;
                } else {
                    if ((DB::connection('mysql_sb')->table('output_defects_packing')->where('kode_numbering', $this->rapidDefect[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rfts_packing')->where('kode_numbering', $this->rapidDefect[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rejects_packing')->where('kode_numbering', $this->rapidDefect[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('temporary_output_packing')->where('kode_numbering', $this->rapidDefect[$i]['numberingInput'])->count()) < 1) {
                        array_push($rapidTemporaryFiltered, [
                            'line_id' => Auth::user()->line_id,
                            'so_det_id' => $this->rapidDefect[$i]['sizeInput'],
                            'no_cut_size' => $this->rapidDefect[$i]['noCutInput'],
                            'kode_numbering' => $this->rapidDefect[$i]['numberingInput'],
                            'size' => $this->rapidDefect[$i]['sizeInputText'],
                            'defect_type_id' => $this->defectType,
                            'defect_area_id' => $this->defectArea,
                            'defect_area_x' => $this->defectAreaPositionX,
                            'defect_area_y' => $this->defectAreaPositionY,
                            'tipe_output' => 'defect',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                        $temporary += 1;
                    } else {
                        $fail += 1;
                    }
                }
            }
        }

        $rapidDefectInsert = DefectModel::insert($rapidDefectFiltered);
        $rapidTemporaryInsert = TemporaryOutput::insert($rapidTemporaryFiltered);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'warning', $temporary." output berhasil terekam di TEMPORARY. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");
        $this->emit('hideModal', 'defect', 'rapid');

        $this->rapidDefect = [];
        $this->rapidDefectCount = 0;
    }

    public function render(SessionManager $session)
    {
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Defect types
        $this->productTypes = ProductType::orderBy('product_type')->get();

        // Defect types
        $this->defectTypes = DefectType::orderBy('defect_type')->get();

        // Defect areas
        $this->defectAreas = DefectArea::orderBy('defect_area')->get();

        // Defect
        $this->defect = DB::connection('mysql_sb')->table('temporary_output_packing')->
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'defect')->
            get();

        return view('livewire.defect-temporary');
    }
}
