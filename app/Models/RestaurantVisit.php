<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantVisit extends Model
{
    use HasFactory;
    protected $fillable = [
        'restro_id',
        'user_id',
        'status'
    ];

   protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function getrestroInfo()
    {
        return $this->belongsTo('App\Models\User', 'restro_id')->select('id','name','is_approved')->with(['getOneRestaurantImage','getRestaurantOtherDetail']);
    }
}
