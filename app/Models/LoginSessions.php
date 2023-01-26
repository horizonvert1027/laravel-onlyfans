<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginSessions extends Model
{
    public $fillable = [
        'user_id',
        'ip',
        'device',
        'device_type',
        'browser',
        'platform',
        'country'
    ];
}
