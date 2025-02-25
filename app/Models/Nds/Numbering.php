<?php

namespace App\Models\Nds;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Numbering extends Model
{
    use HasFactory;

    protected $connection = 'mysql_nds';

    protected $table = 'stocker_numbering';

    protected $guarded = [];
}
