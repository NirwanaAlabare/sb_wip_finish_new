<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\EndlineOutput;
use App\Models\SignalBit\TemporaryOutput;
use App\Models\Nds\Numbering;
use App\Models\Nds\OutputPacking;
use Carbon\Carbon;
use DB;

class RejectTemporary extends Component
{
    public $orderDate;
    public $orderWsDetailSizes;
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
        'numberingInput' => 'required|unique:output_rfts_packing,kode_numbering|unique:output_defects_packing,kode_numbering|unique:output_rejects_packing,kode_numbering|unique:temporary_output_packing,kode_numbering',
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

    public function mount(SessionManager $session, $orderDate, $orderWsDetailSizes)
    {
        $this->ordeDate = $orderDate;
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
                    $this->rapidRft[$i]['numberingInput'] = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
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
            $insertReject = RejectModel::create([
                'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                'so_det_id' => $this->sizeInput,
                'no_cut_size' => $this->noCutInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'NORMAL',
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
            $insertTemp = TemporaryOutput::create([
                'line_id' => Auth::user()->line_id,
                'so_det_id' => $this->sizeInput,
                'no_cut_size' => $this->noCutInput,
                'size' => $this->sizeInputText,
                'kode_numbering' => $this->numberingInput,
                'tipe_output' => 'reject',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertTemp) {
                $this->emit('alert', 'warning', "TANPA MASTER PLAN");
                $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam di TEMPORARY.");

                $this->sizeInput = '';
                $this->sizeInputText = '';
                $this->noCutInput = '';
                $this->numberingInput = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }

            $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
        }

        $this->emit('qrInputFocus', 'reject');
    }

    public function setAndSubmitInput($scannedNumbering) {
        $this->numberingInput = $scannedNumbering;

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
        $rapidTemporaryFiltered = [];
        $success = 0;
        $temporary = 0;
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

                $endlineOutputData = DB::connection('mysql_sb')->table('output_rfts')->where("kode_numbering", $this->rapidReject[$i]['numberingInput'])->first();

                $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $numberingData->so_det_id)->first();
                if (((DB::connection('mysql_sb')->table('output_rejects_packing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rfts_packing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_defects_packing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count()) < 1) && ($thisOrderWsDetailSize) && ($endlineOutputData)) {
                    array_push($rapidRejectFiltered, [
                        'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                        'so_det_id' => $numberingData->so_det_id,
                        'no_cut_size' => $numberingData->no_cut_size,
                        'kode_numbering' => $this->rapidReject[$i]['numberingInput'],
                        'status' => 'NORMAL',
                        'created_by' => Auth::user()->username,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    $success += 1;
                } else {
                    if (((DB::connection('mysql_sb')->table('output_rejects_packing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rfts_packing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_defects_packing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('temporary_output_packing')->where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count()) < 1)) {
                        array_push($rapidTemporaryFiltered, [
                            'line_id' => Auth::user()->line_id,
                            'so_det_id' => $numberingData->so_det_id,
                            'no_cut_size' => $numberingData->no_cut_size,
                            'size' => $numberingData->size,
                            'kode_numbering' => $this->rapidReject[$i]['numberingInput'],
                            'tipe_output' => 'reject',
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
        $rapidRejectInsert = RejectModel::insert($rapidRejectFiltered);
        $temporaryInsert = TemporaryOutput::insert($rapidTemporaryFiltered);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'warning', $temporary." output berhasil terekam di TEMPORARY. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;
    }

    public function render(SessionManager $session)
    {
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Reject
        $this->reject = DB::connection('mysql_sb')->table('temporary_output_packing')->
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'reject')->
            get();

        return view('livewire.reject-temporary');
    }
}
