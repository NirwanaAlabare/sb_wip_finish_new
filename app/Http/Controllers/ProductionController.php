<?php

namespace App\Http\Controllers;

use App\Models\SignalBit\MasterPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id, $method)
    {
        $orderInfo = MasterPlan::selectRaw("
                master_plan.id as id,
                master_plan.tgl_plan as tgl_plan,
                REPLACE(master_plan.sewing_line, '_', ' ') as sewing_line,
                act_costing.kpno as ws_number,
                act_costing.styleno as style_name,
                mastersupplier.supplier as buyer_name,
                so_det.styleno_prod as reff_number,
                master_plan.color as color,
                so_det.size as size,
                so.qty as qty_order,
                CONCAT(masterproduct.product_group, ' - ', masterproduct.product_item) as product_type
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
            ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
            ->where('so_det.cancel', 'N')
            ->where('master_plan.cancel', 'N')
            ->where('master_plan.id', $id)
            ->first();

        $orderWsDetails = MasterPlan::selectRaw("
                master_plan.id as id,
                master_plan.tgl_plan as tgl_plan,
                master_plan.color as color,
                mastersupplier.supplier as buyer_name,
                act_costing.styleno as style_name,
                mastersupplier.supplier as buyer_name
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
            ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
            ->where('so_det.cancel', 'N')
            ->where('master_plan.cancel', 'N')
            ->where('master_plan.sewing_line', str_replace(" ", "_", $orderInfo->sewing_line))
            ->where('act_costing.kpno', $orderInfo->ws_number)
            ->where('master_plan.tgl_plan', $orderInfo->tgl_plan)
            ->groupBy(
                'master_plan.id',
                'master_plan.tgl_plan',
                'master_plan.color',
                'mastersupplier.supplier',
                'act_costing.styleno',
                'mastersupplier.supplier'
            )->get();

        return view('production-panel', ['orderInfo' => $orderInfo, 'orderWsDetails' => $orderWsDetails, 'method' => $method]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LineProductions  $lineProductions
     * @return \Illuminate\Http\Response
     */
    public function show(LineProductions $lineProductions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LineProductions  $lineProductions
     * @return \Illuminate\Http\Response
     */
    public function edit(LineProductions $lineProductions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LineProductions  $lineProductions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LineProductions $lineProductions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LineProductions  $lineProductions
     * @return \Illuminate\Http\Response
     */
    public function destroy(LineProductions $lineProductions)
    {
        //
    }
}
