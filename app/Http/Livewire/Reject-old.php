<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\EndlineOutput;
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

    protected $rules = [
        'outputInput' => 'required|numeric|min:1',
        'sizeInput' => 'required',
    ];

    protected $messages = [
        'outputInput.required' => 'Harap tentukan kuantitas output.',
        'outputInput.numeric' => 'Harap isi kuantitas output dengan angka.',
        'outputInput.min' => 'Kuantitas output tidak bisa kurang dari 1.',
        'sizeInput.required' => 'Harap tentukan ukuran output.',
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes'
    ];

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = null;
    }

    public function updateWsDetailSizes()
    {
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = '';

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);
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

    public function submitInput(SessionManager $session)
    {
        $validatedData = $this->validate();

        $endlineOutputData = EndlineOutput::selectRaw("output_rfts.*")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts.master_plan_id")->where("id_ws", $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->count();
        $currentRftData = Rft::selectRaw("output_rfts_packing.*")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts_packing.master_plan_id")->where('id_ws', $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->count();
        $currentDefectData = Defect::selectRaw("output_defects_packing.*")->leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->where('id_ws', $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->where("defect_status", "defect")->count();
        $currentRejectData = RejectModel::selectRaw("output_rejects_packing.*")->leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->where('id_ws', $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->count();
        $currentOutputData = $currentRftData+$currentDefectData+$currentRejectData;
        $balanceOutputData = $endlineOutputData-$currentOutputData;

        $additionalMessage = $balanceOutputData < $this->outputInput && $balanceOutputData > 0 ? "<b>".($this->outputInput - $balanceOutputData)."</b> output melebihi batas input." : null;
        if ($balanceOutputData < $this->outputInput) {
            $this->outputInput = $balanceOutputData;
        }

        $insertData = [];
        if ($this->outputInput > 0) {
            for ($i = 0; $i < $this->outputInput; $i++)
            {
                array_push($insertData, [
                    'master_plan_id' => $this->orderInfo->id,
                    'so_det_id' => $this->sizeInput,
                    'status' => 'NORMAL',
                    'created_by' => Auth::user()->username,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            $insertReject = RejectModel::insert($insertData);

            if ($insertReject) {
                $getSize = DB::table('so_det')
                    ->select('id', 'size')
                    ->where('id', $this->sizeInput)
                    ->first();

                $this->emit('alert', 'success', $this->outputInput." REJECT output berukuran ".$getSize->size." berhasil terekam.");
                if ($additionalMessage) {
                    $this->emit('alert', 'error', $additionalMessage);
                }

                $this->outputInput = 1;
                $this->sizeInput = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }
        } else {
            $this->emit('alert', 'error', "Output packing-line tidak bisa melebihi endline.");
        }
    }

    public function render(SessionManager $session)
    {
        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Get total output
        $this->output = RejectModel::
            where('master_plan_id', $this->orderInfo->id)->
            count();

        return view('livewire.reject');
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }
}
