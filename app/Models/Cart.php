<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function getCartItems()   {
        return $this->hasMany('App\Models\CartItem','cart_id')->with('getDishDetail');
    }

    
}
