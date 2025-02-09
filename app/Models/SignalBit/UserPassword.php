<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\UserPassword as Authenticatable;

class UserPassword extends Authenticatable
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'userpassword';

    protected $primaryKey = 'line_id';

    protected $fillable = [
        'line_id',
        'username',
        'FullName',
        'Password',
        'password_encrypt',
        'remember_token'
    ];

    public $timestamps = false;

    public function getAuthPassword() {
        return $this->password_encrypt;
    }

    public function masterPlans()
    {
        return $this->hasMany(MasterPlan::class, 'username', 'sewing_line');
    }
}
