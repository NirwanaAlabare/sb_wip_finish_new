<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use App\Models\SignalBit\Rft as RftModel;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\Nds\Numbering;
use App\Models\Nds\OutputPacking;
use App\Models\SignalBit\EndLineOutput;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class RftUniversal extends Component
{
    public $orderDate;
    public $orderWsDetailSizes;
    public $sizeInput;
    public $sizeInputText;
    public $numberingCode;
    public $numberingInput;
    public $rapidRft;
    public $rapidRftCount;
    public $rft;
    public $submitting;

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
        'setAndSubmitInputRft' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function mount(SessionManager $session, $orderDate, $orderWsDetailSizes)
    {
        $this->orderDate = $orderDate;
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->output = 0;
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;
        $this->rapidRft = [];
        $this->rapidRftCount = 0;
        $this->rft = [];
        $this->submitting = false;
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

        if ($panel == 'rft') {
            $this->emit('qrInputFocus', 'rft');
        }
    }

    public function clearInput()
    {
        $this->sizeInput = null;
    }

    public function submitInput()
    {
        $this->emit('qrInputFocus', 'rft');

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
        $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->first();
        if ($endlineOutputData && $thisOrderWsDetailSize) {
            $insertRft = RftModel::create([
                'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                'so_det_id' => $this->sizeInput,
                'no_cut_size' => $this->noCutInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'NORMAL',
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $insertRftNds = OutputPacking::create([
                'sewing_line' => Auth::user()->username,
                'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                'so_det_id' => $this->sizeInput,
                'no_cut_size' => $this->noCutInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'NORMAL',
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertRft) {
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
    }

    public function pushRapidRft($numberingInput, $sizeInput, $sizeInputText) {
        $exist = false;

        foreach ($this->rapidRft as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput)) {
                $exist = true;
            }
        }

        if (!$exist) {
            $this->rapidRftCount += 1;

            if ($numberingInput) {
                if (str_contains($numberingInput, 'WIP')) {
                    $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $numberingInput)->first();
                } else {
                    $numberingCodes = explode('_', $this->numberingInput);

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

                    array_push($this->rapidRft, [
                        'numberingInput' => $numberingInput,
                        'sizeInput' => $sizeInput,
                        'sizeInputText' => $sizeInputText,
                        'noCutInput' => $noCutInput,
                    ]);
                }
            }
        }
    }

    public function submitRapidInput() {
        $rapidRftFiltered = [];
        $rapidRftFilteredNds = [];
        $success = 0;
        $fail = 0;

        if ($this->rapidRft && count($this->rapidRft) > 0) {
            for ($i = 0; $i < count($this->rapidRft); $i++) {
                $endlineOutputCount = DB::connection('mysql_sb')->table('output_rfts')->where("kode_numbering", $this->numberingInput)->count();
                $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $this->rapidRft[$i]['sizeInput'])->first();
                if (($endlineOutputCount > 0) && ((DB::connection('mysql_sb')->table('output_rfts_packing')->where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_defects_packing')->where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rejects_packing')->where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count()) < 1) && ($thisOrderWsDetailSize)) {
                    array_push($rapidRftFiltered, [
                        'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                        'so_det_id' => $this->rapidRft[$i]['sizeInput'],
                        'no_cut_size' => $this->rapidRft[$i]['noCutInput'],
                        'kode_numbering' => $this->rapidRft[$i]['numberingInput'],
                        'status' => 'NORMAL',
                        'created_by' => Auth::user()->username,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    array_push($rapidRftFilteredNds, [
                        'sewing_line' => Auth::user()->username,
                        'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                        'so_det_id' => $numberingData->so_det_id,
                        'no_cut_size' => $numberingData->no_cut_size,
                        'kode_numbering' => $this->rapidRft[$i]['numberingInput'],
                        'status' => 'NORMAL',
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

        $rapidRftInsert = RftModel::insert($rapidRftFiltered);
        $rapidRftInsertNds = OutputPacking::insert($rapidRftFilteredNds);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidRft = [];
        $this->rapidRftCount = 0;
    }

    public function setAndSubmitInput($scannedNumbering) {
        $this->numberingInput = $scannedNumbering;

        $this->submitInput();
    }

    public function render(SessionManager $session)
    {
        if (isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Kode qr sudah discan.")) {
            $this->emit('alert', 'warning', "QR sudah discan.");
        } else if ((isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Harap scan qr.")) || (isset($this->errorBag->messages()['sizeInput']) && collect($this->errorBag->messages()['sizeInput'])->contains("Harap scan qr."))) {
            $this->emit('alert', 'error', "Harap scan QR.");
        }

        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        $this->rft = DB::connection('mysql_sb')->table('output_rfts_packing')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_rfts_packing.master_plan_id')->
            where('master_plan.sewing_line', Auth::user()->username)->
            where('master_plan.tgl_plan', $this->orderDate)->
            where('status', 'NORMAL')->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();

        return view('livewire.rft-universal');
    }
}
