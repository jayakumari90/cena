<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'created_at', 'updated_at'
    // ];

    protected $base_path; 

    public function getUserDetail()   {
        $this->base_path = asset('public/uploads/profile_picture/').'/'; 
        return $this->belongsTo('App\Models\User','user_id')
                    ->select('id',
                            'name',
                            'email',
                            'stripe_customer_id',
                            'notification_setting',
                            \DB::raw('IF(profile_image IS NULL, "", CONCAT("'.$this->base_path.'", profile_image))  AS profile_image')
                        );
    }

    public function getRestroDetail()   {
        return $this->belongsTo('App\Models\User','restro_id')->select('id','name','notification_setting')->with('getOneRestaurantImage','getRestaurantOtherDetail');
    }

     public function getOrderItems()   {
        return $this->hasMany('App\Models\OrderItem','order_id')->with('getDishDetail');
    }

}
