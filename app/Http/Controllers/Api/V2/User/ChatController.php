<?php

namespace App\Http\Controllers\Api\V2\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\BlockedUser;
use App\Models\ReportedUser;
use App\Models\Notification;
use App\Models\User;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ChatController extends Controller
{
    
    public function blockUser(Request $request)
    {
        $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
        $validator = Validator::make($request->all(), [
            'other_user_id' => 'required',
            'isblock' => 'required|in:0,1'
        ]);

        if($validator->fails()){
            $error = $this->validationHandle($validator->messages());
                return response()->json(['status' => false, 'message' => $error]);
        }
        $row = BlockedUser::where('user_id',$user_id)
            ->where('other_user_id',$request->other_user_id)
            ->first();
         if(!$row && (string)$request->isblock == '1'){
            $row = new BlockedUser();
        }
        if($row && (string)$request->isblock == '0'){
           $data = (object) []; 
           $row->delete();
            return response()->json(['status' => true, 'message' => 'User unblocked successfully.','data'=>$data]);
        }
        $row->user_id = $user_id;
        $row->other_user_id = $request->other_user_id;
        $row->save();
        return response()->json(['status' => true, 'message' => 'User blocked successfully.', 'data' =>$row]);
    }

    public function reportUser(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $validator = Validator::make($request->all(), [
                'reported_user_id' => 'required',
                'message' => 'required'
            ]);

            if($validator->fails()){
                $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
            }else{
                $row = ReportedUser::where('user_id',$user_id)
                        ->where('reported_user_id',$request->reported_user_id)
                        ->first();
                if(!$row){
                    $row = new ReportedUser();
                }
                $row->user_id = $user_id;
                $row->reported_user_id = $request->reported_user_id;
                $row->message = $request->message;
                $row->save();
                return response()->json(['status' => true, 'message' => 'User reported successfully.', 'data' =>$row]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function uploadChatImage(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $validator = Validator::make($request->all(), [
                'chat_image' => 'required'
            ]);

            if($validator->fails()){
                $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
            }else{

                 if ($request->file('chat_image')) {

                    $name = 'chat_' . time() . '.' . $request->file('chat_image')->getClientOriginalExtension();
                    $destinationPath = public_path('/uploads/chat_images');
                    $request->file('chat_image')->move($destinationPath, $name);
                    $path = asset('public/uploads/chat_images/').'/'.$name; 

                    return response()->json(['status' => true, 'message' => 'Chat Image Uploaded successfully.', 'data' =>$path]);                           
                }else{
                    return response()->json(['status' => false, 'message' => 'Error Occured.', 'data' =>[]]); 
                }
                
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }


    public function sendChatNotification(Request $request)
    {
        try{
            $user_id = $request->user_id;
            $other_user_id = $request->other_user_id;
            
            $usr = User::where('id',$other_user_id)->select('id','name','notification_setting')->first();
            $message = $usr->name.' sent you a message.';
            if($usr->notification_setting == '1'){
                Notification::saveNotification($user_id,'user',$other_user_id,'CHAT',$message,'');  
            }
                      
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

}

