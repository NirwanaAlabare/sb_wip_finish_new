<?php

namespace App\Http\Livewire\Manual;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework as ReworkModel;
use App\Models\SignalBit\OutputFinishing;
use App\Models\Nds\OutputPacking;
use DB;

class Rework extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // filters
    public $orderInfo;
    public $orderWsDetailSizes;
    public $searchDefect;
    public $searchRework;

    // defect position
    public $defectImage;
    public $defectPositionX;
    public $defectPositionY;

    // defect list
    public $allDefectListFilter;
    public $allDefectImage;
    public $allDefectPosition;
    // public $allDefectList;

    // mass rework
    public $massQty;
    public $massSize;
    public $massDefectType;
    public $massDefectTypeName;
    public $massDefectArea;
    public $massDefectAreaName;
    public $massSelectedDefect;

    public $info;

    protected $listeners = [
        'submitRework' => 'submitRework',
        'submitAllRework' => 'submitAllRework',
        'cancelRework' => 'cancelRework',
        'hideDefectAreaImageClear' => 'hideDefectAreaImage',
        'updateWsDetailSizes' => 'updateWsDetailSizes'
    ];

    // public function fixMissingForeignKey() {
        // $dataRft = DB::select("SELECT output_defects.defect_status defect_status, output_defects.id defect_id, output_defects.master_plan_id defect_mp, output_rfts.`status` rft_status, output_rfts.id rft_id, output_rfts.master_plan_id rft_mp, output_rfts.rework_id rft_rework_id FROM output_rfts
        // left join output_reworks on output_reworks.id = output_rfts.rework_id
        // left join output_defects on output_defects.id = output_reworks.defect_id
        // where output_rfts.master_plan_id IS NULL and output_rfts.`status` = 'rework' and DATE(output_rfts.updated_at) = CURRENT_DATE()");

        // foreach ($dataRft as $rft) {
        //     $rftUpdate = Rft::find($rft->rft_id);
        //     $rftUpdate->master_plan_id = $rft->defect_mp;
        //     $rftUpdate->save();
        //     \Log::info($rftUpdate);
        // }

        // $dataDefect = array_values($dataDefect->toArray());

        // $dataRework = ReworkModel::selectRaw('output_reworks.id as id')->whereRaw("DATE(updated_at) = CURRENT_DATE() and defect_id IS NULL")->orderBy('id', 'asc')->get();

        // $dataRework = array_values($dataRework->toArray());

        // \Log::info($dataRework->count());
        // \Log::info($dataDefect->count());

        // for ($i = 0; $i < 165; $i++) {
        //     $rework = ReworkModel::find($dataRework[$i]['id']);
        //     $rework->defect_id = $dataDefect[$i]['id'];
        //     $rework->save();
        // }
    // }

    public function updateWsDetailSizes()
    {
        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);
    }

    public function loadReworkPage()
    {
        $this->emit('loadReworkPageJs');
    }

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);

        $this->massSize = '';

        $this->info = true;
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

    public function updatingSearchRework()
    {
        $this->resetPage('reworksPage');
    }

    public function submitAllRework() {
        // update defect
        $updateDefect = OutputFinishing::where('master_plan_id', $this->orderInfo->id)->
            where('status', 'defect')->
            update([
                "status" => "reworked",
                "out_at" => Carbon::now()
            ]);

        if ($updateDefect) {
            $this->emit('alert', 'success', "Semua DEFECT berhasil di REWORK");
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT tidak berhasil di REWORK.");
        }
    }

    public function preSubmitMassRework($defectType, $defectArea, $defectTypeName, $defectAreaName) {
        $this->massQty = 1;
        $this->massSize = '';
        $this->massDefectType = $defectType;
        $this->massDefectTypeName = $defectTypeName;
        $this->massDefectArea = $defectArea;
        $this->massDefectAreaName = $defectAreaName;

        $this->emit('showModal', 'massRework');
    }

    public function submitMassRework() {
        $selectedDefect = OutputFinishing::selectRaw('output_check_finishing.*, so_det.size as size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            whereNull('output_check_finishing.kode_numbering')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.defect_type_id', $this->massDefectType)->
            where('output_check_finishing.defect_area_id', $this->massDefectArea)->
            where('output_check_finishing.so_det_id', $this->massSize)->
            take($this->massQty)->get();

        if ($selectedDefect->count() > 0) {
            // update defect
            $defectSql = OutputFinishing::whereIn('id', $selectedDefect->pluck("id"))->update([
                "status" => "reworked",
                "out_at" => Carbon::now(),
            ]);

            if ($selectedDefect->count() > 0) {
                $this->emit('alert', 'success', "DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." berhasil di REWORK sebanyak ".$selectedDefect->count()." kali.");

                $this->emit('hideModal', 'massRework');
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }

    public function submitRework($defectId) {
        // remove from defect
        $defect = OutputFinishing::where('id', $defectId)->
            update([
                "status" => "reworked",
                "out_at" => Carbon::now(),
            ]);

        if ($defect) {
            $this->emit('alert', 'success', "DEFECT dengan ID : ".$defectId." berhasil di REWORK.");
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan ID : ".$defectId." tidak berhasil di REWORK.");
        }
    }

    public function cancelRework($defectId) {
        // add to defect
        $updateDefect = OutputFinishing::where('id', $defectId)->update([
            "status" => "defect",
            "out_at" => null,
        ]);

        if ($updateDefect) {
            $this->emit('alert', 'success', "REWORK dengan ID : ".$defectId." berhasil di kembalikan ke DEFECT.");
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. REWORK ID : ".$defectId." tidak berhasil dikembalikan ke DEFECT.");
        }
    }

    public function render(SessionManager $session)
    {
        $this->emit('loadReworkPageJs');

        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

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

        $reworks = OutputFinishing::selectRaw('output_check_finishing.*, output_defect_types.defect_type, output_defect_areas.defect_area, master_plan.gambar, so_det.size as so_det_size')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_check_finishing.master_plan_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_check_finishing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_check_finishing.defect_type_id')->
            where('output_check_finishing.status', 'reworked')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            whereRaw("(
                output_check_finishing.id LIKE '%".$this->searchRework."%' OR
                so_det.size LIKE '%".$this->searchRework."%' OR
                output_defect_areas.defect_area LIKE '%".$this->searchRework."%' OR
                output_defect_types.defect_type LIKE '%".$this->searchRework."%' OR
                output_check_finishing.status LIKE '%".$this->searchRework."%'
            )")->
            orderBy('output_check_finishing.updated_at', 'desc')->paginate(10, ['*'], 'reworksPage');

        $this->massSelectedDefect = OutputFinishing::selectRaw('output_check_finishing.so_det_id, so_det.size as size, count(*) as total')->
            leftJoin('so_det', 'so_det.id', '=', 'output_check_finishing.so_det_id')->
            where('output_check_finishing.status', 'defect')->
            where('output_check_finishing.master_plan_id', $this->orderInfo->id)->
            where('output_check_finishing.defect_type_id', $this->massDefectType)->
            where('output_check_finishing.defect_area_id', $this->massDefectArea)->
            groupBy('output_check_finishing.so_det_id', 'so_det.size')->get();

        return view('livewire.manual.rework' , ['defects' => $defects, 'reworks' => $reworks, 'allDefectList' => $allDefectList]);
    }
}
