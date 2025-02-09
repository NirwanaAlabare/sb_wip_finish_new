<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_product_types';

    protected $fillable = [
        'id',
        'product_type',
        'image',
        'created_at',
        'updated_at',
    ];

    public function defects()
    {
        return $this->hasMany(Defect::class, 'id', 'product_type_id');
    }
}
