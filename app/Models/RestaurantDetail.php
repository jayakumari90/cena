<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantDetail extends Model
{
    protected $fillable = [
        'user_id',
        'license_number',
        'address',
        'struggling_restaurant',
        'documents',
        'category_id'
    ];

    protected $base_path; 

    public function getRestroImage()
    {
        $this->base_path = asset('public/uploads/restro_images/').'/'; 
       
        return $this->hasOne('App\Models\RestroImage', 'restro_id')->select('restro_id',\DB::raw('IF(image IS NULL, "", CONCAT("'.$this->base_path.'", image))  AS image'));
    }

    public function getStrugglingDocs()
    {
        $this->base_path = asset('public/uploads/struggling_docs/').'/'; 
       
        return $this->hasOne('App\Models\StrugglingDoc', 'restro_id')->select('restro_id',\DB::raw('IF(document IS NULL, "", CONCAT("'.$this->base_path.'", document))  AS document'));
    }
}
