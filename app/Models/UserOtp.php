<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'user_otps';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'email',
        'otp_code',
    ];

}
