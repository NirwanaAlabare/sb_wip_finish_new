<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rework extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reworks_packing';

    protected $fillable = [
        'id',
        'defect_id',
        'status',
        'created_at',
        'updated_at',
    ];

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function defect()
    {
        return $this->hasOne(Defect::class, 'id', 'defect_id');
    }

    public function rft()
    {
        return $this->hasOne(Rft::class, 'rework_id', 'id');
    }

    public function undo()
    {
        return $this->hasOne(Undo::class, 'output_rework_id', 'id');
    }
}
