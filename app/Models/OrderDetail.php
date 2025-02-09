<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb_wip';

    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'product_color',
        'qty',
        'qty_output',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderDetailSizes()
    {
        return $this->hasMany(OrderDetailSize::class);
    }
}
