<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavDish extends Model
{
    use HasFactory;
    protected $fillable = [
        'dish_id',
        'user_id',
        'status'
    ];

   protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function getDishInfo()
    {
        return $this->belongsTo('App\Models\RestroDish', 'dish_id')->select('id','name');
    }

     public function getLikedUserDetail()
    {
        $this->base_path = asset('public/uploads/profile_picture/').'/'; 
        return $this->belongsTo('App\Models\User', 'user_id')
                ->select('id','name',\DB::raw('IF(profile_image IS NULL, "", CONCAT("'.$this->base_path.'", profile_image))  AS profile_image'));
    }
}
