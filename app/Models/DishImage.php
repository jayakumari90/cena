<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DishImage extends Model
{

    use HasFactory;
    protected $fillable = [
        'dish_id',
        'image',
        'thumb'
    ];
    protected $hidden = ['created_at','updated_at'];
     public $timestamps = false;
}
