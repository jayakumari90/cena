<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestroImage extends Model
{

    use HasFactory;
    protected $fillable = [
        'restro_id',
        'image',
        'thumb',
        'created_at',
        'updated_at'
    ];
    protected $hidden = ['created_at','updated_at'];
}
