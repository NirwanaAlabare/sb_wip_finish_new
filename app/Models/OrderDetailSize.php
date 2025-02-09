<?php

namespace App\Models;

use App\Models\Productions\Reject;
use App\Models\Productions\Rework;
use App\Models\Productions\Rft;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailSize extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb_wip';

    protected $table = 'order_detail_sizes';

    protected $fillable = [
        'order_detail_id',
        'product_size',
        'qty',
        'qty_output',
    ];

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function rfts()
    {
        return $this->hasMany(Rft::class);
    }

    public function defects()
    {
        return $this->hasMany(Defect::class);
    }

    public function rejects()
    {
        return $this->hasMany(Reject::class);
    }

    public function Rework()
    {
        return $this->hasMany(Rework::class);
    }
}
