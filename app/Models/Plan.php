<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   // public $timestamps = false;
    protected $hidden = [
        'created_at', 'updated_at'
    ];


    
}
