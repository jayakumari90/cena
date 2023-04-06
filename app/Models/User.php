<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'age',
        'gender',
        'country_code',
        'phone',
        'status',
        'role_id',
        'is_admin',
        'forgot_token',
        'user_type',
        'registration_type',
        'is_two_factoron',
        'stripe_customer_id',
        'stripe_client_id',
    ];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $base_path; 
    protected $base_doc_path; 
    protected $base_thumb_path; 


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function getRestaurantImages()
    {
        $this->base_path = asset('public/uploads/restro_images/').'/'; 
        $this->base_thumb_path = asset('public/uploads/restro_images/thumb/').'/'; 
        return $this->hasMany('App\Models\RestroImage', 'restro_id')->select('restro_id',\DB::raw('IF(image IS NULL, "", CONCAT("'.$this->base_path.'", image))  AS image'),\DB::raw('IF(thumb IS NULL, "", CONCAT("'.$this->base_thumb_path.'", thumb))  AS thumb'));
    }

    public function getOneRestaurantImage()
    {
        $this->base_path = asset('public/uploads/restro_images/').'/'; 
        $this->base_thumb_path = asset('public/uploads/restro_images/thumb/').'/'; 
        return $this->hasOne('App\Models\RestroImage', 'restro_id')->select('restro_id',\DB::raw('IF(image IS NULL, "", CONCAT("'.$this->base_path.'", image))  AS image'),\DB::raw('IF(thumb IS NULL, "", CONCAT("'.$this->base_thumb_path.'", thumb))  AS thumb'));
    }

    public function getStrugglingDocs()
    {
        $this->base_doc_path = asset('public/uploads/struggling_docs/').'/'; 
        return $this->hasMany('App\Models\StrugglingDoc', 'restro_id')->select('restro_id',\DB::raw('IF(document IS NULL, "", CONCAT("'.$this->base_doc_path.'", document))  AS document'));
    }

    public function getRegistergDocs()
    {
        $this->base_doc_path = asset('public/uploads/register_docs/').'/'; 
        return $this->hasMany('App\Models\RegisterDoc', 'restro_id')->select('restro_id',\DB::raw('IF(document IS NULL, "", CONCAT("'.$this->base_doc_path.'", document))  AS document'));
    }

    public function getRestaurantDishes()
    {
        return $this->hasMany('App\Models\RestroDish', 'restro_id')->with('getcategory','getDishImages');
    }

    public function getRestaurantOtherDetail()
    {
        return $this->hasOne('App\Models\RestaurantDetail','user_id');
    }

    
}
