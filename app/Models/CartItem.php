<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
   public $timestamps = false;

    public function getDishDetail()   {
        return $this->belongsTo('App\Models\RestroDish','dish_id')->select('id','name','restro_id')->with('getDishImages','getrestro');
    }



}
