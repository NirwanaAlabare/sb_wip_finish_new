<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework as ReworkModel;
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
        $allDefect = Defect::selectRaw('output_defects_packing.id id, output_defects_packing.master_plan_id master_plan_id, output_defects_packing.so_det_id so_det_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
            where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->get();

        if ($allDefect->count() > 0) {
            $rftArray = [];
            $rftNdsArray = [];
            foreach ($allDefect as $defect) {
                // create rework
                $createRework = ReworkModel::create([
                    "defect_id" => $defect->id,
                    "status" => "NORMAL",
                    "created_by" => Auth::user()->username
                ]);

                // add rft array
                array_push($rftArray, [
                    'master_plan_id' => $defect->master_plan_id,
                    'so_det_id' => $defect->so_det_id,
                    "status" => "REWORK",
                    "rework_id" => $createRework->id,
                    "created_by" => Auth::user()->username,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);

                // add rft nds array
                array_push($rftNdsArray, [
                    'sewing_line' => $this->orderInfo->sewing_line,
                    'master_plan_id' => $defect->master_plan_id,
                    'so_det_id' => $defect->so_det_id,
                    "status" => "REWORK",
                    "rework_id" => $createRework->id,
                    "created_by" => Auth::user()->username,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }

            // update defect
            $defectSql = Defect::where('master_plan_id', $this->orderInfo->id)->update([
                "defect_status" => "reworked"
            ]);

            // create rft
            $createRft = Rft::insert($rftArray);
            $createRftNds = OutputPacking::insert($rftNdsArray);

            if ($allDefect->count() > 0) {
                $this->emit('alert', 'success', "Semua DEFECT berhasil di REWORK");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
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
        $selectedDefect = Defect::selectRaw('output_defects_packing.*, so_det.size as size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
            where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
            where('output_defects_packing.defect_type_id', $this->massDefectType)->
            where('output_defects_packing.defect_area_id', $this->massDefectArea)->
            where('output_defects_packing.so_det_id', $this->massSize)->
            take($this->massQty)->get();

        if ($selectedDefect->count() > 0) {
            $rftArray = [];
            $rftNdsArray = [];
            $defectIds = [];
            foreach ($selectedDefect as $defect) {
                // create rework
                $createRework = ReworkModel::create([
                    "defect_id" => $defect->id,
                    "status" => "NORMAL",
                    "created_by" => Auth::user()->username
                ]);

                // add defect id array
                array_push($defectIds, $defect->id);

                // add rft array
                array_push($rftArray, [
                    'master_plan_id' => $defect->master_plan_id,
                    'so_det_id' => $defect->so_det_id,
                    "status" => "REWORK",
                    "rework_id" => $createRework->id,
                    "created_by" => Auth::user()->username,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);

                // add rft nds array
                array_push($rftNdsArray, [
                    'sewing_line' => $this->orderInfo->sewing_line,
                    'master_plan_id' => $defect->master_plan_id,
                    'so_det_id' => $defect->so_det_id,
                    "status" => "REWORK",
                    "rework_id" => $createRework->id,
                    "created_by" => Auth::user()->username,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }
            // update defect
            $defectSql = Defect::whereIn('id', $defectIds)->update([
                "defect_status" => "reworked"
            ]);

            // create rft
            $createRft = Rft::insert($rftArray);
            // create rft nds
            $createRftNds = OutputPacking::insert($rftNdsArray);

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
        $thisDefectRework = ReworkModel::where('defect_id', $defectId)->count();

        if ($thisDefectRework < 1) {
            // add to rework
            $createRework = ReworkModel::create([
                "defect_id" => $defectId,
                "status" => "NORMAL",
                "created_by" => Auth::user()->username
            ]);

            // remove from defect
            $defect = Defect::where('id', $defectId);
            $getDefect = $defect->first();
            $updateDefect = $defect->update([
                "defect_status" => "reworked"
            ]);

            // add to rft
            $createRft = Rft::create([
                'master_plan_id' => $getDefect->master_plan_id,
                'so_det_id' => $getDefect->so_det_id,
                "status" => "REWORK",
                "rework_id" => $createRework->id,
                "created_by" => Auth::user()->username
            ]);

            // add to rft
            $createRftNds = OutputPacking::create([
                'sewing_line' => $this->orderInfo->sewing_line,
                'master_plan_id' => $getDefect->master_plan_id,
                'so_det_id' => $getDefect->so_det_id,
                "status" => "REWORK",
                "rework_id" => $createRework->id,
                "created_by" => Auth::user()->username
            ]);

            if ($createRework && $updateDefect && $createRft) {
                $this->emit('alert', 'success', "DEFECT dengan ID : ".$defectId." berhasil di REWORK.");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan ID : ".$defectId." tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'warning', "Pencegahan data redundant. DEFECT dengan ID : ".$defectId." sudah ada di REWORK.");
        }
    }

    public function cancelRework($reworkId, $defectId) {
        // delete from rework
        $deleteRework = ReworkModel::where('id', $reworkId)->delete();

        // add to defect
        $defect = Defect::where('id', $defectId);
        $getDefect = $defect->first();
        $updateDefect = $defect->update([
            "defect_status" => "defect"
        ]);

        // delete from rft
        $deleteRft = Rft::where('rework_id', $reworkId)->delete();

        // delete from rft nds
        $deleteRftNds = OutputPacking::where('rework_id', $reworkId)->delete();

        if ($deleteRework && $updateDefect && $deleteRft) {
            $this->emit('alert', 'success', "REWORK dengan REWORK ID : ".$reworkId." dan DEFECT ID : ".$defectId." berhasil di kembalikan ke DEFECT.");
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. REWORK dengan REWORK ID : ".$reworkId." dan DEFECT ID : ".$defectId." tidak berhasil dikembalikan ke DEFECT.");
        }
    }

    public function render(SessionManager $session)
    {
        $this->emit('loadReworkPageJs');

        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        $this->allDefectImage = MasterPlan::select('gambar')->find($this->orderInfo->id);

        $this->allDefectPosition = Defect::where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
            get();

        $allDefectList = Defect::selectRaw('output_defects_packing.defect_type_id, output_defects_packing.defect_area_id, output_defect_types.defect_type, output_defect_areas.defect_area, count(*) as total')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects_packing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')->
            where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
            whereRaw("
                (
                    output_defect_types.defect_type LIKE '%".$this->allDefectListFilter."%' OR
                    output_defect_areas.defect_area LIKE '%".$this->allDefectListFilter."%'
                )
            ")->
            groupBy('output_defects_packing.defect_type_id', 'output_defects_packing.defect_area_id', 'output_defect_types.defect_type', 'output_defect_areas.defect_area')->
            orderBy('output_defects_packing.updated_at', 'desc')->
            paginate(5, ['*'], 'allDefectListPage');

        $defects = Defect::selectRaw('output_defects_packing.*, so_det.size as so_det_size')->
            leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects_packing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')->
            where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
            whereRaw("(
                output_defects_packing.id LIKE '%".$this->searchDefect."%' OR
                so_det.size LIKE '%".$this->searchDefect."%' OR
                output_defect_areas.defect_area LIKE '%".$this->searchDefect."%' OR
                output_defect_types.defect_type LIKE '%".$this->searchDefect."%' OR
                output_defects_packing.defect_status LIKE '%".$this->searchDefect."%'
            )")->
            orderBy('output_defects_packing.updated_at', 'desc')->paginate(10, ['*'], 'defectsPage');

        $reworks = ReworkModel::selectRaw('output_reworks_packing.*, so_det.size as so_det_size')->
            leftJoin('output_defects_packing', 'output_defects_packing.id', '=', 'output_reworks_packing.defect_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects_packing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
            where('output_defects_packing.defect_status', 'reworked')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
            whereRaw("(
                output_reworks_packing.id LIKE '%".$this->searchRework."%' OR
                output_defects_packing.id LIKE '%".$this->searchRework."%' OR
                so_det.size LIKE '%".$this->searchRework."%' OR
                output_defect_areas.defect_area LIKE '%".$this->searchRework."%' OR
                output_defect_types.defect_type LIKE '%".$this->searchRework."%' OR
                output_defects_packing.defect_status LIKE '%".$this->searchRework."%'
            )")->
            orderBy('output_reworks_packing.updated_at', 'desc')->paginate(10, ['*'], 'reworksPage');

        $this->massSelectedDefect = Defect::selectRaw('output_defects_packing.so_det_id, so_det.size as size, count(*) as total')->
            leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
            where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
            where('output_defects_packing.defect_type_id', $this->massDefectType)->
            where('output_defects_packing.defect_area_id', $this->massDefectArea)->
            groupBy('output_defects_packing.so_det_id', 'so_det.size')->get();

        return view('livewire.rework' , ['defects' => $defects, 'reworks' => $reworks, 'allDefectList' => $allDefectList]);
    }
}
