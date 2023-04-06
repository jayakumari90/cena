<?php

use App\Models\User;
use App\Models\Device;
use App\Models\Setting;
use App\Mail\TestEmail;
use Intervention\Image\ImageManagerStatic as Image;
// use Mail;4


    function startQueryLog()
    {
        \DB::enableQueryLog();
    }

    

    function showQueries()
    {
        dd(\DB::getQueryLog());
    }

    function sendMail($mailtemplate,$data, $subject){
        Mail::send($mailtemplate, ['data'=>$data], function ($message) use ($subject, $data) {
                                $message->from('cenasender@gmail.com', 'Cena');
                                $message->to($data['email'], env('APP_NAME', 'CENA'))->subject($subject);
                            });
        return true;
    }

    

    function chkAuthToken($token) {
      
       $chkToken = Setting::whereRaw("auth_token='".$token."'")->count();
        if($chkToken > 0){    
            return $chkToken;

        } else{
           return false;
        }    
      
    }

    function doUpload($file,$path,$thumb=false,$pre=null,$id=null) {
     
        $response = [];
        $image = $file;
        //print_r($image);
        if($id!=null){
            $file = $id.'.'.$image->getClientOriginalExtension();
        }else{
            $file = $pre.time().rand().'.'.$image->getClientOriginalExtension();
        }
        $destinationPath = public_path().'/'.$path; 
        Image::make($image)->save($destinationPath.$file,70);
        $thumbPath = '';
        if($thumb==true){
            $thumbPath = public_path($path).'thumb/'.$file;
            if(!file_exists(public_path($path).'thumb/')) {
              mkdir(public_path($path).'thumb/', 0777, true);
            }
            $cropInfo = Image::make($image)->heighten(200)->save($thumbPath);
        }
        $response['status']     = true;
        $response['file']       = $file;
        $response['thumb']       = $file;
        $response['file_name']  = $file;
        $response['path']       = $path; 
        // print_r($response);die;
        return $response;
      
    }

    

