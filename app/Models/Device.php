<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_device';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'device_id', 'device_type','device_uniqueid','isdont_askon'
    ];

     public static function manageDeviceIdAndToken($user_id, $device_id, $device_type,$device_uniqueid,$donot_ask='', $methodName) {

        if ($methodName == 'add') {

            $row = Device::where(['user_id' => $user_id,'device_uniqueid' => $device_uniqueid])->first();

            if($row){
                $row->device_id = $device_id;
                $row->device_type = $device_type;
                if($donot_ask!=''){
                    $row->isdont_askon = $donot_ask;
                }
                $row->save();
            }else{
                $row = new Device;
                $row->user_id = $user_id;
                $row->device_id = $device_id;
                $row->device_type = $device_type;
                $row->device_uniqueid = $device_uniqueid;
                $row->isdont_askon = $donot_ask;
                $row->save();
            }
            
        }
        if ($methodName == 'remove') {
            Device::where('user_id', $user_id)
                    ->where('device_id', $device_token)
                    ->where('device_type', $device_type)
                    ->delete();
        }
    }

    public static function getAndUpdateBadgecount($device_id){
        $badge_count = 0;
        $device_row = Device::where('device_id', $device_id)->first();
        if(!empty($device_row)){
            $badge_count = !empty($device_row->badge_count) ? $device_row->badge_count : 0;
            $badge_count += 1;
            $device_row->badge_count = $badge_count;
            $device_row->save();
        }
       
        return $badge_count;
    }
    
}
