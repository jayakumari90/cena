<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Lib\PushNotification;
use DB;
class Notification extends Model
{
    protected $base_path; 
    public static function saveNotification($user_id,$user_type, $ref_id, $ref_type, $message,$message_title)
    {
    	$notification = new Notification();
        $notification->user_id = $user_id;
    	$notification->ref_id = $ref_id;
        $notification->ref_type = $ref_type;
        $notification->message = $message;
    	$notification->notification_title = $message_title;
    	$notification->save();

        if($ref_type == 'CHAT'){
            PushNotification::chatNotify($notification, $dic = []);
        }else{
            PushNotification::Notify($notification,$user_type, $dic = []);
        }
    	

    }

     public function getRefDetail()   {
        $this->base_path = asset('public/uploads/profile_picture/').'/'; 
        return $this->belongsTo('App\Models\User','ref_id')->select('id','name',\DB::raw('IF(profile_image IS NULL, "", CONCAT("'.$this->base_path.'", profile_image))  AS profile_image'));
    }

}