<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestroDish extends Model
{

    use HasFactory;

    protected $fillable = [
        'restro_id',
        'category_id',
        'name',
        'price',
        'description',
        'discount',
        'is_popular',
    ];

    protected $base_thumb_path; 

    protected $hidden = ['created_at','updated_at'];
    public function getrestro()
    {
        return $this->belongsTo('App\Models\User', 'restro_id')->select('id','name')->with('getOneRestaurantImage');
    }

    public function getcategory()
    {
        return $this->belongsTo('App\Models\DishCategory', 'category_id');
    }

    public function getDishImages()
    {
         $this->base_thumb_path = asset('public/uploads/dish_images/').'/'; 
        return $this->hasMany('App\Models\DishImage', 'dish_id')->select('dish_id',\DB::raw('IF(thumb IS NULL, "", CONCAT("'.$this->base_thumb_path.'", thumb))  AS thumb'));
    }

    public function getLikedUsers()
    {
        return $this->hasMany('App\Models\FavDish','dish_id')
        ->where('status','1')
        ->with('getLikedUserDetail');
    }

    
}
