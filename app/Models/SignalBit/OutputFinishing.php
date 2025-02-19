<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputFinishing extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_check_finishing';

    protected $fillable = [
        'id',
        'so_det_id',
        'master_plan_id',
        'status',
        'defect_type_id',
        'defect_area_id',
        'defect_area_x',
        'defect_area_y',
        'kode_numbering',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function defectType()
    {
        return $this->belongsTo(DefectType::class, 'defect_type_id', 'id');
    }

    public function defectArea()
    {
        return $this->belongsTo(DefectArea::class, 'defect_area_id', 'id');
    }
}
