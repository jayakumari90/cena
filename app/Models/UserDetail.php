<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'user_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        
        'user_id',
        'user_preference',
        'gender',
        'age',
        'radius',
        'about_us',
        'created_at',
        'updated_at'
    ];

}
