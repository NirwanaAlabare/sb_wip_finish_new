<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\OutputFinishing;
use Livewire\WithPagination;
use DB;

class ProfileContent extends Component
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

        $totalDefectSql = OutputFinishing::select('output_check_finishing.*')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
            where('output_check_finishing.status', 'defect');
            if (Auth::user()->Groupp != 'ALLSEWING') {
                $totalDefectSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $totalDefectSql->where('master_plan.id', $this->masterPlan);
            }
        $totalDefect = $totalDefectSql->whereRaw("DATE(output_check_finishing.created_at) >= '".$this->dateFrom."'")->
            whereRaw("DATE(output_check_finishing.created_at) <= '".$this->dateTo."'")->
            count();

        $totalRejectSql = OutputFinishing::select('output_check_finishing.*')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
            where('output_check_finishing.status', 'rejected');
            if (Auth::user()->Groupp != 'ALLSEWING') {
                $totalRejectSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $totalRejectSql->where('master_plan.id', $this->masterPlan);
            }
        $totalReject = $totalRejectSql->whereRaw("DATE(output_check_finishing.created_at) >= '".$this->dateFrom."'")->
            whereRaw("DATE(output_check_finishing.created_at) <= '".$this->dateTo."'")->
            count();

        $totalReworkSql = OutputFinishing::select('output_check_finishing.*')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
            where('master_plan.sewing_line', Auth::user()->username)->
            where('output_check_finishing.status', 'reworked');
            if (Auth::user()->Groupp != 'ALLSEWING') {
                $totalReworkSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $totalReworkSql->where('master_plan.id', $this->masterPlan);
            }
        $totalRework = $totalReworkSql->whereRaw("DATE(output_check_finishing.created_at) >= '".$this->dateFrom."'")->
            whereRaw("DATE(output_check_finishing.created_at) <= '".$this->dateTo."'")->
            count();

        return view('livewire.profile-content', [
            'totalRft' => $totalRft,
            'totalDefect' => $totalDefect,
            'totalReject' => $totalReject,
            'totalRework' => $totalRework,
        ]);
    }
}
