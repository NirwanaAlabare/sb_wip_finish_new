<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\OutputFinishing;

class HistoryContent extends Component
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

        // $latestOutputRfts = Rft::selectRaw('
        //         output_rfts_packing.updated_at,
        //         so_det.size as size,
        //         count(output_rfts_packing.id) as total
        //     ')->
        //     leftJoin('master_plan', 'master_plan.id', '=', 'output_rfts_packing.master_plan_id')->
        //     leftJoin('so_det', 'so_det.id', '=', 'output_rfts_packing.so_det_id')->
        //     where('output_rfts_packing.status', 'normal');
        //     if (Auth::user()->Groupp != 'ALLSEWING') {
        //         $latestOutputRfts->where('master_plan.sewing_line', Auth::user()->username);
        //     }
        //     if ($this->masterPlan) {
        //         $latestOutputRfts->where('master_plan.id', $this->masterPlan);
        //     }
        // $latestRfts = $latestOutputRfts->whereRaw("DATE(output_rfts_packing.created_at) BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
        //     whereRaw("master_plan.tgl_plan BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
        //     groupBy("output_rfts_packing.updated_at", "so_det.size")->
        //     orderBy("output_rfts_packing.updated_at", "desc")->
        //     orderBy("output_rfts_packing.created_at", "desc")->
        //     limit("5")->get();

        $latestOutputDefects = OutputFinishing::selectRaw('
                output_check_finishing.updated_at,
                output_defect_types.defect_type,
                output_defect_areas.defect_area,
                master_plan.gambar,
                output_check_finishing.defect_area_x,
                output_check_finishing.defect_area_y,
                so_det.size as size,
                count(*) as total')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'defect');
            if (Auth::user()->Groupp != 'ALLSEWING') {
                $latestOutputDefects->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $latestOutputDefects->where('master_plan.id', $this->masterPlan);
            }
        $latestDefects = $latestOutputDefects->whereRaw("DATE(output_check_finishing.created_at) BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            whereRaw("master_plan.tgl_plan BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            groupBy(
                "output_check_finishing.updated_at",
                "output_defect_types.defect_type",
                "output_defect_areas.defect_area",
                "master_plan.gambar",
                "output_check_finishing.defect_area_x",
                "output_check_finishing.defect_area_y",
                "so_det.size"
            )->
            orderBy("output_check_finishing.updated_at", "desc")->
            orderBy("output_check_finishing.created_at", "desc")->
            limit("5")->get();

        $latestOutputRejects = OutputFinishing::selectRaw('output_check_finishing.updated_at, so_det.size as size, count(*) as total')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('master_plan.sewing_line', Auth::user()->username)->
            where('output_check_finishing.status', 'rejected');
            if (Auth::user()->Groupp != 'ALLSEWING') {
                $latestOutputRejects->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $latestOutputRejects->where('master_plan.id', $this->masterPlan);
            }
        $latestRejects = $latestOutputRejects->whereRaw("DATE(output_check_finishing.created_at) BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            whereRaw("master_plan.tgl_plan BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            groupBy("output_check_finishing.updated_at", "so_det.size")->
            orderBy("output_check_finishing.updated_at", "desc")->
            orderBy("output_check_finishing.created_at", "desc")->
            limit("5")->get();

        $latestOutputReworks = OutputFinishing::selectRaw('
                output_check_finishing.updated_at,
                output_defect_types.defect_type,
                output_defect_areas.defect_area,
                master_plan.gambar,
                output_check_finishing.defect_area_x,
                output_check_finishing.defect_area_y,
                so_det.size as size,
                count(*) as total
            ')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('master_plan.sewing_line', Auth::user()->username)->
            where('output_check_finishing.status', 'reworked');
            if (Auth::user()->Groupp != 'ALLSEWING') {
                $latestOutputReworks->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $latestOutputReworks->where('master_plan.id', $this->masterPlan);
            }
        $latestReworks = $latestOutputReworks->whereRaw("DATE(output_check_finishing.created_at) BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            whereRaw("master_plan.tgl_plan BETWEEN '".$this->dateFrom."' AND '".$this->dateTo."'")->
            groupBy(
                "output_check_finishing.updated_at",
                "output_defect_types.defect_type",
                "output_defect_areas.defect_area",
                "master_plan.gambar",
                "output_check_finishing.defect_area_x",
                "output_check_finishing.defect_area_y",
                "so_det.size"
            )->
            orderBy("output_check_finishing.updated_at", "desc")->
            orderBy("output_check_finishing.created_at", "desc")->
            limit("5")->get();

        return view('livewire.history-content', [
            // 'latestOutput' => $latestOutput,
            // 'latestRfts' => $latestRfts,
            'latestDefects' => $latestDefects,
            'latestRejects' => $latestRejects,
            'latestReworks' => $latestReworks
        ]);
    }
}
