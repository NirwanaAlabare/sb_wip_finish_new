<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UndoFinishing extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_undo_finishing';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'output_id',
        'output_type',
        'keterangan',
        'undo_by',
        'created_at',
        'updated_at',
    ];

    public function outputCheckFinishing()
    {
        return $this->hasOne(OutputFinishing::class, 'id', 'output_id');
    }

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'id', 'master_plan_id');
    }
}
