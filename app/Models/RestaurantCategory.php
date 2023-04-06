<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantCategory extends Model
{
	protected $fillable = [
        'category_name',
        'created_at',
        'updated_at'
    ];

   protected $hidden = [
        'created_at', 'updated_at'
    ];
}
