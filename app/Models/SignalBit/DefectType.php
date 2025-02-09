<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefectType extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_defect_types';

    protected $fillable = [
        'id',
        'defect_type',
        'created_at',
        'updated_at',
        'hidden',
    ];

    public function defects()
    {
        return $this->hasMany(Defect::class, 'id', 'defect_area_id');
    }
}
