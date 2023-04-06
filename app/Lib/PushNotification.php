<?php

namespace App\Lib;
use Illuminate\Support\Facades\Log;
class PushNotification {

    public static function Notify($row,$user_type,$dic = []) {
     
       $device_arr = [];
       $notification_info = [];
       if($user_type == 'restaurant'){
            $model = '\App\Models\RestaurantDevice';
            $table = 'restaurant_devices';
       }else{
            $model = '\App\Models\Device';
            $table = 'user_device';
       }
        
            
        $device_arr = $model::select($table.'.*')
            ->leftJoin('users', 'users.id', '=', $table.'.user_id')
            ->whereIn('device_type', array('ANDROID','IOS'))
            ->where('user_id', $row->user_id)
            ->pluck('device_id')->toArray();

        if (!empty($device_arr)) {
            
            if($row->ref_type == 'MATCH'){
                $notification_info = array('id'=>$row->ref_id,'ref_type'=>$row->ref_type,'ref_detail'=>$row->getRefDetail);
            }else{
                $notification_info = array('id'=>$row->ref_id,'ref_type'=>$row->ref_type);
            }

            PushNotification::sendFcmNotify($model,$device_arr, $row->message,$row->message_title,$notification_info,$row->ref_type);
        }
        
    }

     public static function chatNotify($row,$message,$dic = []) {
     
       $device_arr = [];
                    
        $device_arr =\App\Models\Device::select('user_device.*')
            ->leftJoin('users', 'users.id', '=','user_device.user_id')
            ->whereIn('device_type', array('ANDROID','IOS'))
            ->where('user_id', $row->user_id)
            ->pluck('device_id')->toArray();

        if (!empty($device_arr)) {
            $model = '\App\Models\Device';
            $notification_info = array('id'=>$row->ref_id,'ref_type'=>$row->ref_type,'ref_detail'=>$row->getRefDetail);
            PushNotification::sendFcmNotify($model,$device_arr, $row->message,$row->message_title,$notification_info,'CHAT');
        }
        
    }

    public static  function sendFcmNotify($model_name,$user_devices, $message,$message_title, $dictionary = '', $type = '' , $sound = '',$role='')
    {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $server_key = 'AAAAJuUFhZI:APA91bF_pcQ9KajGA1s7Ge4SGovsBUuBlCo1Q_TKcoEoaqRKOp-A4HZkXQAaq2JQik3Ivqf_wbvjDaIbZADW4VbNS9RG2Rc5X0xqZtb6Psno0hI42rfz2KnWFxhBFRefmzerzMwVk0-X';

        $ttl = 86400;
        // $ttl = 86400;
        $randomNum = rand(10, 100);
      
        $fields = [];
         foreach ($user_devices as  $device_id) {

            $badge_count = $model_name::getAndUpdateBadgecount($device_id);

             $fields = array
            (
                'priority'     => "high",
                'data'         => array( "title"=>$message_title, "body" =>$message,'sound' => 'default','type'=>$type,'content-available'=>1,'dictionary' => $dictionary,"badge" => $badge_count,"click_action" => "FLUTTER_NOTIFICATION_CLICK"),

                'notification'         => array( "title"=>$message_title,"message"=>$message, "body" =>$message,'sound' => 'default','type'=>$type,'content-available'=>1,'dictionary' => $dictionary,"badge" => $badge_count,"click_action" => "FLUTTER_NOTIFICATION_CLICK"),

                "badge" => $badge_count,
                 "to"    =>$device_id
            ); 
            $headers = array(
                        'Content-Type:application/json',
                        'Authorization:key='.$server_key
                    );
        
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            // Log::info($fields);
        //        echo '<pre>'; print_r($result);
        // echo '<pre>'; print_r($user_devices);die('test');

            if ($result === FALSE) {
               die('Problem occurred: ' . curl_error($ch));
            }
            
        }        
    }

   
}
