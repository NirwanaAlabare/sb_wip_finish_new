<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reject extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_rejects_packing';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'status',
        'created_at',
        'updated_at',
    ];

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function undo()
    {
        return $this->hasOne(Undo::class, 'output_reject_id', 'id');
    }
}
