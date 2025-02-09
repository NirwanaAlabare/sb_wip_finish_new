<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineProduction extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb_wip';

    protected $table = 'line_productions';

    protected $fillable = [
        'line_id',
        'order_id',
        'qty',
        'qty_output'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function line()
    {
        return $this->belongsTo(Line::class);
    }
}
