<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SignalBit\Undo;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\MasterPlan;
use App\Models\Nds\OutputPacking;

class UndoContent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $masterPlan;
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $masterPlan = session()->get('orderInfo');
        $this->masterPlan = $masterPlan ? $masterPlan->id : null;
        $this->dateFrom = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
        $this->dateTo = $this->dateTo ? $this->dateTo : date('Y-m-d');
    }

    public function restoreUndo()
    {
        $restoreData = Undo::selectRaw("*, output_undo_packing.id as undo_id")->leftJoin("master_plan", "master_plan.id", "=", "output_undo_packing.master_plan_id")->where("master_plan.tgl_plan", [$this->dateFrom, $this->dateTo])->get();
        $rft = [];
        $rftNds = [];
        $defect = [];
        $rework = [];
        $reject = [];
        $deleteUndoIds = [];

        foreach ($restoreData as $restore) {
            if ($restore->output_rft_id) {
                array_push($rft, [
                    "master_plan_id" => $restore->master_plan_id,
                    "so_det_id" => $restore->so_det_id,
                    'status' => 'NORMAL',
                    "created_by" => Auth::user()->username,
                    "created_at" => $restore->created_at,
                    "updated_at" => $restore->updated_at,
                ]);

                array_push($rftNds, [
                    "master_plan_id" => $restore->master_plan_id,
                    "so_det_id" => $restore->so_det_id,
                    'status' => 'NORMAL',
                    "sewing_line" => Auth::user()->username,
                    "created_by" => Auth::user()->username,
                    "created_at" => $restore->created_at,
                    "updated_at" => $restore->updated_at,
                ]);
            }

            if ($restore->output_defect_id) {
                array_push($defect, [
                    "master_plan_id" => $restore->master_plan_id,
                    "so_det_id" => $restore->so_det_id,
                    'status' => 'NORMAL',
                    "created_at" => $restore->created_at,
                    "updated_at" => $restore->updated_at,
                ]);
            }

            if ($restore->output_rework_id) {
                $createDefect = Defect::create([
                    "master_plan_id" => $restore->master_plan_id,
                    "so_det_id" => $restore->so_det_id,
                    'status' => 'REWORK',
                    "created_at" => $restore->created_at,
                    "updated_at" => $restore->updated_at,
                ]);

                array_push($rework, [
                    "defect_id" => $createDefect->id,
                    "status" => "NORMAL",
                    "created_at" => $restore->created_at,
                    "updated_at" => $restore->updated_at,
                ]);
            }

            if ($restore->output_reject_id) {
                array_push($reject, [
                    "master_plan_id" => $restore->master_plan_id,
                    "so_det_id" => $restore->so_det_id,
                    "created_at" => $restore->created_at,
                    "updated_at" => $restore->updated_at,
                ]);
            }

            array_push($deleteUndoIds, $restore->undo_id);
        }

        if (count($rft) > 0) {
            Rft::insert($rft);
            OutputPacking::insert($rft);
        }

        if (count($defect) > 0) {
            Defect::insert($defect);
        }

        if (count($reject) > 0) {
            Reject::insert($reject);
        }

        if (count($rework) > 0) {
            Rework::insert($rework);
        }

        if (count($deleteUndoIds) > 0) {
            Undo::whereIn("id", $deleteUndoIds)->delete();
        }

        $this->emit('alert', [$rft, $defect, $rework, $reject]);
    }

    public function render()
    {
        $masterPlan = session()->get('orderInfo');
        $this->masterPlan = $masterPlan ? $masterPlan->id : null;

        // $latestOutput = DB::select(DB::raw("
        //     SELECT output_rfts.created_at, output_rfts.updated_at FROM output_rfts
        //     LEFT JOIN master_plan ON master_plan.id = output_rfts.master_plan_id
        //     WHERE master_plan.sewing_line = '".Auth::user()->username."'
        //     UNION
        //     SELECT output_defects.created_at, output_defects.updated_at FROM output_defects
        //     LEFT JOIN master_plan ON master_plan.id = output_defects.master_plan_id
        //     WHERE master_plan.sewing_line = '".Auth::user()->username."'
        //     UNION
        //     SELECT output_rejects.created_at, output_rejects.updated_at FROM output_rejects
        //     LEFT JOIN master_plan ON master_plan.id = output_rejects.master_plan_id
        //     WHERE master_plan.sewing_line = '".Auth::user()->username."'
        //     UNION
        //     SELECT output_reworks.created_at, output_reworks.updated_at FROM output_reworks
        //     LEFT JOIN output_defects ON output_defects.id = output_reworks.defect_id
        //     LEFT JOIN master_plan ON master_plan.id = output_defects.master_plan_id
        //     WHERE master_plan.sewing_line = '".Auth::user()->username."'
        //     ORDER BY updated_at DESC, created_at DESC
        //     LIMIT 10
        // "));

        $latestUndoSql = Undo::selectRaw('output_undo_packing.updated_at, output_undo_packing.keterangan, so_det.size as size, count(*) as total')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_undo_packing.master_plan_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_undo_packing.so_det_id');
            if (Auth::user()->Groupp != 'ALLSEWING') {
                $latestUndoSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $latestUndoSql->where('master_plan.id', $this->masterPlan);
            }
        $latestUndo = $latestUndoSql->whereRaw("DATE(output_undo_packing.created_at) BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            whereRaw("master_plan.tgl_plan BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            groupBy("output_undo_packing.updated_at", "output_undo_packing.keterangan", "so_det.size")->
            orderBy("output_undo_packing.updated_at", "desc")->
            orderBy("output_undo_packing.created_at", "desc")->
            paginate(10, ['*'], 'latestUndoPage');

        return view('livewire.undo-content', [
            // 'latestOutput' => $latestOutput,
            'latestUndo' => $latestUndo,
        ]);
    }
}
