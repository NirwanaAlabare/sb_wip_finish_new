<?php

namespace App\Models\Nds;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputPacking extends Model
{
    use HasFactory;

    protected $connection = 'mysql_nds';

    protected $table = 'output_rfts_packing';

    protected $guarded = [];
}
