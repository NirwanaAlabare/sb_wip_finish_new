<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Session\SessionManager;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\ProductType;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;

class DefectHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // order info
    public $orderInfo;
    public $orderWsDetailSizes;

    // filter
    public $filterDefectSize;
    public $filterDefectType;
    public $filterDefectArea;
    public $filterDefectStatus;
    public $search;

    // defect position
    public $productTypeImage;
    public $defectPositionX;
    public $defectPositionY;

    protected $listeners = [
        'hideDefectAreaImageClear' => 'hideDefectAreaImage'
    ];

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    public function setDefectAreaPosition($x, $y)
    {
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;
    }

    public function showDefectAreaImage($productTypeImage, $x, $y)
    {
        $this->productTypeImage = $productTypeImage;
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;

        $this->emit('showDefectAreaImage', $this->productTypeImage, $this->defectPositionX, $this->defectPositionY);
    }

    public function hideDefectAreaImage()
    {
        $this->productTypeImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render(SessionManager $session)
    {
        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);
        $productTypes = ProductType::get();
        $defectTypes = DefectType::get();
        $defectAreas = DefectArea::get();
        $defects = Defect::selectRaw('
                output_defects_packing.updated_at,
                so_det.size as so_det_size,
                master_plan.gambar,
                output_defects_packing.defect_area_x,
                output_defects_packing.defect_area_y,
                output_defect_types.defect_type,
                output_defect_areas.defect_area,
                output_defects_packing.defect_status,
                count(*) as total
            ')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_defects_packing.master_plan_id')->
            leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
            leftJoin('output_product_types', 'output_product_types.id', '=', 'output_defects_packing.product_type_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects_packing.defect_area_id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id);

        if ($this->filterDefectSize != null && $this->filterDefectSize != 'all') {
            $defects->where('so_det.id', $this->filterDefectSize);
        }

        if ($this->filterDefectType != null && $this->filterDefectType != 'all') {
            $defects->where('output_defect_types.id', $this->filterDefectType);
        }

        if ($this->filterDefectArea != null && $this->filterDefectArea != 'all') {
            $defects->where('output_defect_areas.id', $this->filterDefectArea);
        }

        if ($this->filterDefectStatus != null && $this->filterDefectStatus != 'all') {
            $defects->where('output_defects_packing.defect_status', $this->filterDefectStatus);
        }

        $filteredDefects = $defects->whereRaw("(
            output_defects_packing.id LIKE '%".$this->search."%' OR
            so_det.size LIKE '%".$this->search."%' OR
            output_defect_areas.defect_area LIKE '%".$this->search."%' OR
            output_defect_types.defect_type LIKE '%".$this->search."%' OR
            output_defects_packing.defect_status LIKE '%".$this->search."%'
        )")->
        groupBy(
            'output_defects_packing.updated_at',
            'so_det.size',
            'master_plan.gambar',
            'output_defect_types.defect_type',
            'output_defect_areas.defect_area',
            'output_defects_packing.defect_area_x',
            'output_defects_packing.defect_area_y',
            'output_defects_packing.defect_status'
        )->
        orderBy('output_defects_packing.updated_at', 'desc')->paginate(10);

        return view('livewire.defect-history', ['defects' => $filteredDefects, 'productTypes' => $productTypes, 'defectTypes' => $defectTypes, 'defectAreas' => $defectAreas]);
    }
}
