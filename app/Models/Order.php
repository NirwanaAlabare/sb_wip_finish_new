<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb_wip';

    protected $table = 'orders';

    protected $fillable = [
        'ws_number',
        'buyer_name',
        'style_name',
        'product_type',
        'qty',
        'qty_output',
    ];

    public function lineProductions()
    {
        return $this->hasMany(LineProduction::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(orderDetail::class);
    }
}
