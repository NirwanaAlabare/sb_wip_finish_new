<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Undo extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_undo_packing';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'output_rft_id',
        'output_defect_id',
        'output_reject_id',
        'output_rework_id',
        'keterangan',
        'created_at',
        'updated_at',
    ];

    public function rft()
    {
        return $this->hasOne(Rft::class, 'id', 'output_rft_id');
    }

    public function defect()
    {
        return $this->hasOne(Defect::class, 'id', 'output_defect_id');
    }

    public function reject()
    {
        return $this->hasOne(Reject::class, 'id', 'output_reject_id');
    }

    public function rework()
    {
        return $this->hasOne(Rework::class, 'id', 'output_rework_id');
    }
}
