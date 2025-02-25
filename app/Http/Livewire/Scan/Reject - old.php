<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\EndLineOutput;
use App\Models\SignalBit\OutputFinishing;
use App\Models\Nds\Numbering;
use Carbon\Carbon;
use DB;

class Reject extends Component
{
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

    protected $rules = [
        'sizeInput' => 'required',
        'noCutInput' => 'required',
        'numberingInput' => 'required|unique:output_rfts_packing,kode_numbering|unique:output_defects_packing,kode_numbering|unique:output_rejects_packing,kode_numbering',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'noCutInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.',
        'numberingInput.unique' => 'Kode qr sudah discan.',
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'setAndSubmitInputReject' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->sizeInput = null;

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;
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

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        if ($panel == 'reject') {
            $this->emit('qrInputFocus', 'reject');
        }
    }

    public function updateOutput()
    {
        // Get total output
        $this->output = DB::connection('mysql_sb')->table('output_check_finishing')->
            where('status', 'rejected')->
            where('master_plan_id', $this->orderInfo->id)->
            count();

        // Reject
        $this->reject = DB::connection('mysql_sb')->table('output_check_finishing')->
            where('status', 'rejected')->
            where('master_plan_id', $this->orderInfo->id)->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();
    }

    public function clearInput()
    {
        $this->sizeInput = null;
        $this->noCutInput = null;
        $this->numberingInput = null;
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

        $endlineOutputData = DB::connection('mysql_sb')->table('output_rfts')->where("kode_numbering", $this->numberingInput)->first();

        if ($endlineOutputData && $this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->count() > 0) {
            $insertReject = OutputFinishing::create([
                'master_plan_id' => $this->orderInfo->id,
                'so_det_id' => $this->sizeInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'rejected',
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertReject) {
                $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam.");

                $this->sizeInput = '';
                $this->sizeInputText = '';
                $this->noCutInput = '';
                $this->numberingInput = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
        }

        $this->emit('qrInputFocus', 'reject');
    }

    public function setAndSubmitInput($scannedNumbering, $scannedSize, $scannedSizeText) {
        $this->numberingInput = $scannedNumbering;
        $this->sizeInput = $scannedSize;
        $this->sizeInputText = $scannedSizeText;

        $this->submitInput();
    }

    public function pushRapidReject($numberingInput, $sizeInput, $sizeInputText) {
        $exist = false;

        foreach ($this->rapidReject as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput)) {
                $exist = true;
            }
        }

        if (!$exist) {
            $this->rapidRejectCount += 1;

            if ($numberingInput) {
                array_push($this->rapidReject, [
                    'numberingInput' => $numberingInput,
                ]);
            }
        }
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

                    if (count($numberingCodes) > 2) {
                        $this->rapidReject[$i]['numberingInput'] = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                        $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $this->rapidReject[$i]['numberingInput'])->first();
                    } else {
                        $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $this->rapidReject[$i]['numberingInput'])->first();
                    }
                }

                $endlineOutputCount = DB::connection('mysql_sb')->table('output_check_finishing')->where("kode_numbering", $this->rapidReject[$i]['numberingInput'])->count();

                if (($endlineOutputCount > 0) && ((DB::connection('mysql_sb')->table('output_check_finishing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count()) < 1) && ($this->orderWsDetailSizes->where('so_det_id', $numberingData->so_det_id)->count() > 0)) {
                    array_push($rapidRejectFiltered, [
                        'master_plan_id' => $this->orderInfo->id,
                        'so_det_id' => $numberingData->so_det_id,
                        'no_cut_size' => $numberingData->no_cut_size,
                        'kode_numbering' => $this->rapidReject[$i]['numberingInput'],
                        'status' => 'rejected',
                        'created_by' => Auth::user()->username,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    $success += 1;
                } else {
                    $fail += 1;
                }
            }
        }

        $rapidRejectInsert = RejectModel::insert($rapidRejectFiltered);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;
    }

    public function render(SessionManager $session)
    {
        if (isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Kode qr sudah discan.")) {
            $this->emit('alert', 'warning', "QR sudah discan.");
        } else if ((isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Harap scan qr.")) || (isset($this->errorBag->messages()['sizeInput']) && collect($this->errorBag->messages()['sizeInput'])->contains("Harap scan qr."))) {
            $this->emit('alert', 'error', "Harap scan QR.");
        }

        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Get total output
        $this->output = DB::connection('mysql_sb')->table('output_rejects_packing')->
            where('master_plan_id', $this->orderInfo->id)->
            count();

        // Reject
        $this->reject = DB::connection('mysql_sb')->table('output_rejects_packing')->
            where('master_plan_id', $this->orderInfo->id)->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();

        return view('livewire.scan.reject');
    }
}
