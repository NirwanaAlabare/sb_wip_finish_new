<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Line as Authenticatable;

class Line extends Authenticatable
{
    use HasFactory;

    protected $connection = 'mysql_sb_wip';

    protected $table = 'lines';

    protected $fillable = [
        'name',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function lineProductions()
    {
        return $this->hasMany(LineProduction::class);
    }
}
