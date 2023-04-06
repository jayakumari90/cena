<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLikeDislike extends Model
{
    use HasFactory;
    protected $fillable = [
        'other_user_id',
        'user_id',
        'is_like'
    ];

   protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function getOtheruserInfo()
    {
        return $this->belongsTo('App\Models\User', 'other_user_id')->select('id','name','notification_setting');
    }

    public function getuserInfo()
    {
        return $this->belongsTo('App\Models\User', 'user_id')->select('id','name','notification_setting');
    }
}
