<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public $timestamps = false;

    public function getDishDetail()   {
        return $this->belongsTo('App\Models\RestroDish','dish_id')->select('id','name');
    }
    
}
