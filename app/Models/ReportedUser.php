<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportedUser extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    public $timestamps = false;

     public function getUserDetail()   {
        return $this->belongsTo('App\Models\User','user_id')
                    ->select('id',
                            'name',
                            'email',
                            
                        );
    }

    public function getReportedUserDetail()   {
        return $this->belongsTo('App\Models\User','reported_user_id')
                    ->select('id',
                            'name',
                            'email',
                            
                        );
    }
}
