<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\File;


use App\Models\User;
use App\Models\Device;
use App\Models\Setting;
use App\Models\UserOtp;
use App\Models\UserDetail;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\BlockedUser;
use App\Models\reportedUser;
use App\Lib\Stripe;
use ReceiptValidator\iTunes\Validator as iTunesValidator;
use ReceiptValidator\GooglePlay\Validator as PlayValidator;
use App\Lib\PushNotification;

class UserController extends Controller
{
    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public $response = ['status' => false, 'message' => "", 'data' => null];
    public $status_code = 200;

     /*
        2. Check User
    */
    public function checkUser(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $base_url =  url('/');
                $validator = Validator::make($request->all(), [
                    'email' => 'required|string|email',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json(array('status' => false, 'message' => $this->response));
                }else{
                    $otp = mt_rand(100000, 999999);
                    $user = User::where('email', $request->email)->first();
                    if($user){
                        return response()->json(['status' => false, 'message' => 'Email already exist']);
                    }else{
                        UserOtp::create([
                            'email'=>$request->email,
                            'otp_code'=>$otp
                        ]);
                        $data = [
                                'email' => $request->email,
                                'name' => '',
                                'base_url' => $base_url,
                                'otp' => $otp
                            ];
                            //Send email
                            $email_subject = 'Verfication Mail';
                            sendMail('email.registermail',$data,$email_subject);
                
                            return response()->json(['status' => true, 'message' => 'Otp for verification has been sent to your mail. Check your inbox.', 'data' => [],'otp'=>$otp]);
                    }
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    /*
        3. signup
    */

    public function register(Request $request)
    {
       
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            //print_r($chkToken);die('fffff');
            if($chkToken == 1){
                $base_url =  url('/');
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'age'=>'required',
                    'email' => 'required|string|email|unique:users,email',
                    'password' => 'required|min:8',
                    'gender' => 'required',
                    'otp' => 'required',
                    'device_type' => 'required|in:ANDROID,IOS',
                    'device_id' => 'required',
                    'device_uniqueid'=>'required',
                    'isdont_askon'=>'required'
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }
                
                $hash = md5($request->email) . time();

                 //generate stripe customer id
                $customer_token = Stripe::createCustomer($request->get('email')); 
                // print_r($customer_token);die;
                if($customer_token['status']==1){
                    $stripe_customer_id = $customer_token['customer_id'];
                }

                $user = User::create([
                    'name'      =>  $request->name,
                    'email'     =>  $request->email,
                    'password'  =>  Hash::make($request->password),
                    'forgot_token'    =>  $hash,
                    'status'    =>  1,
                    'user_type'   =>  1,
                    'stripe_customer_id'   =>  isset($stripe_customer_id)? $stripe_customer_id:null,
                ]); 
                // $user_detail = UserDetail::create([
                //     'user_id'       =>  $user->id,
                //     'age'       =>  $request->age,
                //     'gender'    =>  $request->gender,
                //     'about_us'    =>  $request->about_us,
                //     'preferred_age' =>  '18-30',
                //     'radius'    =>  '100',
                // ]); 
                $user_detail = new UserDetail;
                $user_detail->user_id = $user->id; 
                $user_detail->age = $request->age; 
                $user_detail->gender = $request->gender; 
                $user_detail->about_us = $request->about_us; 
                $user_detail->preferred_age = '18-30'; 
                $user_detail->preferred_gender = 'B'; 
                $user_detail->radius = '100'; 
                $user_detail->save();
                //dd($user_detail);  
                if($user && $user_detail){
                    //update device
                    Device::manageDeviceIdAndToken($user->id,$request->device_id,$request->device_type,$request->device_uniqueid,$request->isdont_askon,'add');

                    //delete otp
                    UserOtp::where(['otp_code'=> $request->otp,'email'=> $request->email])->delete();

                    $user = $this->showUserDetails($user->id);
                    $user->token = JWTAuth::fromUser($user); 
                        
                    DB::commit();
                    return response()->json(['status' => true, 'message' => 'Successfully Registered', 'data' => $user]);
                }else{
                    return response()->json(['status' => false, 'message' => 'Something went wrong', 'data' => []]);
                }
          
            }else{
                return response()->json(['status' => false, 'message' => 'Invalid auth token', 'data' => []]);
            } 
        }catch (Exception $e) {
               // DB::rollBack();
                return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
            }
    }

    
    /*
        1. Login
    */
    public function login(Request $request)
    {
        try{
             $headers = apache_request_headers();
             $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'email' => 'required|string|email',
                    'password' => 'required',
                    'device_type' => 'required',
                    'device_id' => 'required',
                    'device_uniqueid' => 'required',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json(array('status' => false, 'message' => $this->response));
                }

                $user = User::where(['email' => $request->get('email'), 'user_type' => 1])->first();
                if (!$user) {
                    $this->response['message'] = "User does not exist.";
                    return response()->json($this->response, $this->status_code);
                }else if($user->status == 0){
                    $this->response['message'] = "Your account is not active,so please contact to admin for account activation.";
                    return response()->json($this->response, $this->status_code);
                }else if (!Hash::check($request->get('password'), $user->password)) {
                    $this->response['message'] = "Password is not correct.";
                    return response()->json($this->response, $this->status_code);
                }else{
                    //update user device
                    Device::manageDeviceIdAndToken($user->id,$request->device_id,$request->device_type,$request->device_uniqueid,'','add');
                    
                    //get device detail
                    $device = Device::where(['user_id' => $user->id, 'device_id' => $request->get('device_id'),'device_uniqueid'=>$request->device_uniqueid])->first();

                    //get user detail
                    $user = $this->showUserDetails($user->id);

                    $token = JWTAuth::fromUser($user);
                    $user->token = $token;
                    $user->isdont_askon = $device->isdont_askon;
                    $user->device_uniqueid = $device->device_uniqueid;


                    $this->response["status"] = true;
                    $this->response['message'] = "Successfully Logged In";
                    $this->response["data"] = $user;
                    return response()->json($this->response, $this->status_code);
                }                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid Auth Token']);
            }
            
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

     public function showUserDetails($id)
     {

        $base_path = asset('public/uploads/profile_picture/').'/'; 
        $row = User::leftJoin('user_details', function($join) {
                  $join->on('users.id', '=', 'user_details.user_id');
        })
        ->leftJoin('plans', function($join) {
                  $join->on('users.plan_id', '=', 'plans.id');
        })
        ->where('users.id',$id)
        ->select('users.id',
            'users.name',
            'users.email',
            'users.user_type',
            'users.registration_type',
            'users.is_two_factoron',
            'users.notification_setting',
            'users.stripe_customer_id',
            'users.stripe_user_id',
            'users.plan_id',
            'plans.name as plan_name',
            'users.is_premium',
            'user_details.age',
            'user_details.gender',
            'user_details.about_us',
            'user_details.user_profession',
            'user_details.user_company',
            'user_details.user_preference',
            'user_details.preferred_gender',
            'user_details.preferred_age',
            'user_details.radius',
            \DB::raw('IF(profile_image IS NULL, "", CONCAT("'.$base_path.'", profile_image))  AS profile_image'),
            'users.social_id',
            'users.social_type'
        )
        ->first();
        return $row;
    }


    public function twoFactorCode(Request $request){
        try{

            $base_url =url('/');
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                
                $validator = Validator::make($request->all(), [
                    'is_two_factoron' => 'required|in:0,1',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }
                $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                $user = User::where('id',$user_id)->first();
                $user->is_two_factoron = $request->is_two_factoron;
                $user->save();
                return response()->json(['status' => true, 'message' =>'success','data'=>$user]);
                
                
            }else{
                return response()->json(['status' => false, 'message' =>'Invailid auth token']);
            }
        }catch(\Throwable $th){
            $this->status_code = 500;
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);

        }
    }

    public function verifyUser(Request $request){
        try{
            $headers = apache_request_headers();//
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                        'device_uniqueid' => 'required|string|max:255',
                        'isdont_askon' => 'required',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }
                $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                $chkDevice = Device::where(['user_id'=>$user_id,'device_uniqueid'=>$request->device_uniqueid])->first();
                if($chkDevice){
                    $chkDevice->isdont_askon = $request->isdont_askon;
                    $chkDevice->save();
                    return response()->json(['status' => true, 'message' =>'success','data'=>$chkDevice]);
                    
                }
            }else{
                return response()->json(['status' => false, 'message' =>'Invailid auth token']);
            }
        }catch(\Throwable $th){
            $this->status_code = 500;
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);

        }
    }

     public function sendOtp(Request $request){
        try{

            $base_url =url('/');
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                //DB::beginTransaction();

                $validator = Validator::make($request->all(), [
                    'email' => 'required',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }

                $otp = mt_rand(100000, 999999);
                $user = User::where('email',$request->email)->select('id','email','name')->first();
       
                if($user){
                    $addotp = UserOtp::create([
                        'email'=>$request->email,
                        'otp_code'=>$otp
                    ]);

                    $data = [
                        'email' => $user->email,
                        'name' =>  $user->name,
                        'base_url' => $base_url,
                        'otp' => $otp
                    ];
                    $email_subject = "Otp for verification";
                    sendMail('email.mail',$data,$email_subject);
                    return response()->json(['status' => true, 'message' =>'otp send on your register email','otp'=>$otp]);

                }else{
                    return response()->json(['status' => false, 'message' =>'User not found.']);
                }
                
            }else{
                return response()->json(['status' => false, 'message' =>'Invailid auth token']);
            }
        }catch(\Throwable $th){
            $this->status_code = 500;
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);

        }
    }

    /*
        4. Social Login
    */
    public function socialLogin(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'social_id' => 'required|string|max:255',
                    'social_type' => 'required|in:FACEBOOK,GOOGLE,APPLE',
                    
                    'device_id' => 'required|string|max:255',
                    'device_type' => 'required|string|max:255',
                    'device_uniqueid' => 'required|string|max:255',

                    // 'name' => 'required',
                    //'email' => 'nullable|email'   
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }else{
                    if(isset($request->email) && $request->email!=''){
                        $user = User::where('email','=',$request->email)->first();
                    }else{
                        $user = User::where('social_id','=',$request->social_id)->first();
                    }
                    if(isset($user) && $user->status!=1){
                        return response()->json(['status' => false, 'message' =>'User is deactived by admin.']);
                    }
                    if(!$user){
                        $user = new User;
                        $user->name = (!empty($request->get('name')))?$request->get('name'):'';
                        $user->email = (!empty($request->get('email')))?$request->get('email'):'';
                        $user->status = '1';
                        $user->user_type = '1';
                        $user->registration_type = 'social';
                        //generate stripe customer id
                        $customer_token = Stripe::createCustomer($request->get('email')); 
                        // print_r($customer_token);die;
                        if($customer_token['status']==1){
                            $user->stripe_customer_id = $customer_token['customer_id'];
                        }

                        if($request->profile_picture)
                        {
                            $image_data = $this->saveImageFromUrl($request->profile_picture, '');
                            if($image_data){
                                $user->profile_image = $image_data['file_name'];
                            }                               
                        }

                        $user->save();
                        $user_detail = new UserDetail();
                        $user_detail->user_id = $user->id;
                        $user_detail->save();

                    }
                    if($user){
                        //update user device
                        Device::manageDeviceIdAndToken($user->id,$request->device_id,$request->device_type,$request->device_uniqueid,'','add');
                        
                        //update social detail
                        User::where('id',$user->id)->update(
                                ['social_id'=>$request->social_id,
                                'social_type'=>$request->social_type,
                                ]);
                        //get user detail
                        $user = $this->showUserDetails($user->id);

                        $token = JWTAuth::fromUser($user);
                        $user->token = $token;

                        $this->response["status"] = true;
                        $this->response['message'] = "Successfully Logged In";
                        $this->response["data"] = $user;
                        return response()->json($this->response, $this->status_code);
                    }else{
                        return response()->json(['status' => false, 'message' =>'Error Occured']);
                    }
                }//end else

            }else{
                return response()->json(['status' => false, 'message' =>'Invailid auth token']);
            }
        }catch(\Throwable $th){
            return response()->json(['status' => false, 'message' => $th->getMessage().$th->getLine(), 'data' => []]);
        }
    }
    public static function saveImageFromUrl($image_url, $path, $pre=''){
        $response = [];
        $file = $pre.time().rand(100000,10000000).'.jpg';
        $save_path = public_path('uploads/profile_picture').$path.$file;
        @copy($image_url, $save_path);
        
        $response['status'] = true;
        $response['file'] = $path.$file;
        $response['file_name'] = $file;
        $response['path'] = $path;
        
        return $response;
    }


    /**
     * 5.
     * Forgot password
    */
    public function forgetPassword(Request $request)
    {
        try {
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $base_url =  url('/');
                $data = $request->all();
                $otp = mt_rand(100000, 999999);
                $validator = Validator::make($data, [
                    'email' => 'required|email',
                ]);

                // if validation fails.
                if ($validator->fails()) {
                    $response['status'] = false;
                    $response['message'] = $validator->errors()->first();
                    $response['data'] = [];
                    return $response;
                } else {
                    $users = User::where('email', $data['email'])
                            ->select('id','status','email','name')
                            ->first();

                    if ($users) {
                        // If user is active.
                        if ($users->status == 1) {

                            // generate random token insert in user table & send with url.
                            $hash = md5($users->email) . time();
                            $addotp = UserOtp::create([
                                            'email'=>$request->email,
                                            'otp_code'=>$otp
                                        ]);
                        

                            $data = [
                                'email' => $users->email,
                                'name' => $users->name,
                                'base_url' => $base_url,
                                'otp' => $otp
                            ];
                            //Send email
                            $email_subject = 'Reset Password';
                            sendMail('email.forgotPassmail',$data,$email_subject);
                            

                            return response()->json(['status' => true, 'message' => 'Otp for reset password has been sent to your mail. Check your inbox.','otp'=>$otp]);
                        } else {
                            $error_message = 'Your account is not activated. Check your email account for activation link.';
                            return response()->json(['status' => false, 'message' => $error_message, 'is_verified' => false]);
                        }
                    } else {
                        return response()->json(['status' => false, 'message' => 'No account exists with this email. Please register first.', 'data' => []]);
                    }
                }
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    /*
        6. Reset Password
    */
    public function resetPassword(Request $request)
    {

        try {
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'email'=>'required',
                    'otp'=>'required',
                    'password' => 'required|min:8'
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response, $this->status_code);
                }

                $userOtp = UserOtp::where(['otp_code'=> $request->otp,'email'=> $request->email])
                ->first();

                if ($userOtp) {
                    $user = User::where('email',$request->email)
                            ->where('user_type',1)
                            ->first();

                    if($user){
                        if (Hash::check($request->password, $user->password)) {
                            
                            $this->response['status'] = false;
                            $this->response['message'] = "New password cannot be same as old password.";
                            return response()->json($this->response, $this->status_code);
                        }else{
                            
                            $user->password = Hash::make($request->password);
                            $user->save();
                            

                            //delete otp
                            UserOtp::where(['otp_code'=> $request->otp,'email'=> $request->email])->delete();

                            $this->response['status'] = true;
                            $this->response['message'] = "Reset Password Successfully";
                            return response()->json($this->response, $this->status_code);
                        }
                    }else {
                        $this->response['status'] = false;
                        $this->response['message'] = "User not found.";
                        return response()->json($this->response, $this->status_code);
                    }
                    
                } else {
                    $this->response['message'] = "Invailid otp.";
                    return response()->json($this->response, $this->status_code);
                }
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    /*
        7. Get Profile
    */

    public function getProfile(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                $user = $this->showUserDetails($user_id);
                if($user){
                    return response()->json(['status' => true, 'message' => 'Success', 'data' => $user]);
                }
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch(\Throwable $th){
            $this->status_code = 500;
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);

        }
    }   

    /*
        8. Edit Profile
    */

    public function editProfile(Request $request)
    {
        try {
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'age' => 'required',
                    'gender' => 'required',
                ]);
                 if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response, $this->status_code);
                }
                 else {
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

                    //  Find user
                    $users = User::whereId($user_id)->first();
            
                    if ($users) {
                        $chkEmail = 0;
                            if($request->email || ($users->email=='' && $users->email==null)){
                                $chkEmail = User::where('email', $request->email)->where('id','!=',$user_id)->count();
                            }
                            
                            if($chkEmail>0) {
                                return response()->json(['status' => true, 'message' => 'Email already exists.', 'data' => null]);   
                            }else{
                               if ($request->file('profile_image')) {

                                    //delete old file

                                    $img = public_path().'/uploads/profile_picture/'.$users->profile_image;
                                   
                                    if(File::exists($img)) {
                                        File::delete($img);
                                    }

                                    $name = 'user_' . time() . '.' . $request->file('profile_image')->getClientOriginalExtension();
                                    $destinationPath = public_path('/uploads/profile_picture');
                                    $request->file('profile_image')->move($destinationPath, $name);
                                    $users->profile_image = $name;                            
                                }
                                $users->name    = (isset($request->name)) ? $request->name : $users->name;
                                
                                $users->email    = isset($request->email) ? $request->email : $users->email ;
                                                  
                                if($users->save())
                                {
                                    $usr_detail = UserDetail::where('user_id',$users->id)->first();
                                    $usr_detail->age    = (isset($request->age)) ? $request->age : $users->age;
                                    $usr_detail->gender   = (isset($request->gender)) ? $request->gender : $users->gender;                        
                                    $usr_detail->user_profession   = (isset($request->user_profession)) ? $request->user_profession : "";                        
                                    $usr_detail->user_company   = (isset($request->user_company)) ? $request->user_company : "";
                                    $usr_detail->about_us   = (isset($request->about_us)) ? $request->about_us : "";

                                    $usr_detail->save();

                                    $user = $this->showUserDetails($user_id);
                                    return response()->json(['status' => true, 'message' => 'Profile updated successfully.', 'data' => $user]);

                                }else{
                                    return response()->json(['status' => false, 'message' => 'Something went wrong.', 'data' => []]);
                                }
                                
                            }
                      
                        
                    } else {
                        return response()->json(['status' => false, 'message' => 'User not found.', 'data' => []]);
                    }
                }
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    /*
        9. Change Password
    */
    public function changePassword(Request $request)
    {

        $user_id = JWTAuth::parseToken()->authenticate()->id;
        try {
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'current_password' => 'required|string',
                    'password' => 'required|string|min:8',
                ]);

                if ($validator->fails()) {
                    $response['status'] = false;
                    $response['message'] = $validator->errors()->first();
                    $response['data'] = [];
                    $response = array('status' => false, 'response' => $response);
                    return $response;
                }
                $user = User::whereId($user_id)->first();
                //print_r($user);die('sds');
                if ($user) {
                    
                    if (!Hash::check($request->get('current_password'), $user->password)) {
                        return response()->json(['status'=>false, 'message'=> "Current password is not correct.",'data'=>[]]);
                    }

                    if (Hash::check($request->get('password'), $user->password)) {
                        return response()->json(['status'=>false, 'message'=> "New and old password can not be same.",'data'=>[]]);
                    } else {
                        $user->password = Hash::make($request->get('password'));
                        $user->save();
                        return response()->json(['status'=>true, 'message'=>'Password updated successfully']);
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'User does not exists.']);
                }
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    /*
        10. User Preferance
    */
    public function userPreferance(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'user_preference' => 'required'
                ]);

                if ($validator->fails()) {
                    $response['status'] = false;
                    $response['message'] = $validator->errors()->first();
                    $response['data'] = [];
                    $response = array('status' => false, 'response' => $response);
                    return $response;
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $row = UserDetail::where('user_id',$user_id)->first();
                $row->user_id = $user_id;
                if($request->age){
                    $row->age =  $request->age ;
                }
                 if($request->gender){
                    $row->gender = $request->gender;
                }
                $row->user_preference = $request->user_preference;
                $row->save();

                return response()->json(['status' => true, 'message' => 'User preference added succssfully.', 'data' => $row]);
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }
    
    /*
        12. logout
    */

    public function logout(Request $request)
    {
        $headers = apache_request_headers();
        $chkToken = chkAuthToken($headers['auth_token']);
        if($chkToken == 1){
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

            $userDevice = Device::where(['user_id'=>$user_id,'device_uniqueid'=>$request->device_uniqueid])->update(['device_id'=>null,'device_type'=>null]);
            JWTAuth::invalidate();
            $this->response['status'] = true;
            $this->response['message'] = "Successfully Logged Out.";
            $this->status_code = 200;
            return response()->json($this->response, $this->status_code);
        }else{

            return response()->json(['status' => false, 'message' => 'Invailid auth token']);
        }
    }

    public function updateUserBadge(Request $request)
    {
       try
       {
          $data = $request->all();
          $validator = Validator::make($data, 
            [
              'device_id' => 'required',
            ]);
          if ($validator->fails()) 
          {
            
            $error = $this->validationHandle($validator->messages());
            return response()->json(['status' => false, 'message' => $error]);
          } 
          else 
          {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

            $device = Device::where('user_id',$user_id)->where('device_id',$request->device_id)->first();
            if($device){
                $device->badge_count = 0;
                $device->save();
                return response()->json(['status' => true,'message'=>'Badge updated successfully.']);
            }else{
                return response()->json(['status' => true,'message'=>'Record not found']);
            }                
          }
       }
       catch(\Exception $e){
         return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
       }
        
    }

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
           $row->delete();
            return response()->json(['status' => true, 'message' => 'User unblocked successfully.','data'=>[]]);
        }
        $row->user_id = $user_id;
        $row->other_user_id = $request->other_user_id;
        $row->save();
        return response()->json(['status' => true, 'message' => 'User blocked successfully.', 'data' =>[]]);
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

    public function getAuthToken(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'access_token' => 'required',
                
            ]);

            if ($validator->fails()) {
                $response['status'] = false;
                $response['message'] = $validator->errors()->first();
                $response['data'] = [];
                $response = array('status' => false, 'response' => $response);
                return $response;
            }
            $access_token = 'cena_app_uri';
            if($access_token == $request->access_token){
                $auth = Setting::select('auth_token')->first();
                return response()->json(['status' => true, 'message' => 'success', 'data' => $auth]);

            } else{
               return response()->json(['status' => false, 'message' => 'Invailid access token', 'data' => $auth]); 
            }
            

        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }
    public  function versionControl(){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){    
                $setting = Setting::select('id','is_maintenance','ios_version','android_version','is_ios_force_update','is_android_force_update')->first();
                return response()->json(['status' => true, 'message' => 'success', 'data' => $setting]);
            }else{
            return response()->json(['status' => false, 'message' => 'Invailid auth token']);

            }
        }catch(\Throwable $th){
            $this->status_code = 500;
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);

        }
    }

    /* Facebook deletion*/

    public function removeData(Request $request)
    {
        $signed_request = $request->signed_request;
        $data = $this->parse_signed_request($signed_request);
        $user_id = $data['user_id'];

        // Start data deletion
        $users = SocialAccount::where(['social_id' => $user_id])->first();
        $uid = $users->user_id;

        User::where('id', $uid)->delete();
        //------------------------

        $status_url = url('/deletion/' . $uid); //'https://www.<your_website>.com/deletion?id=abc123'; // URL to track the deletion
        $confirmation_code = $uid; // unique code for the deletion request

        $data = array(
            'url' => $status_url,
            'confirmation_code' => $confirmation_code
        );
        return json_encode($data);
    }

    function parse_signed_request($signed_request)
    {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        $secret = "327aff204f09d4be22fb27f16c1e2f9a"; // Use your app secret here

        // decode the data
        $sig = $this->base64_url_decode($encoded_sig);
        $data = json_decode($this->base64_url_decode($payload), true);

        // confirm the signature
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
            error_log('Bad Signed JSON signature!');
            return null;
        }

        return $data;
    }

    function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }


    public function base64($data) {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }

    public function getSubscriptionPlans(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $rows = Plan::where('plan_type','User')->get();
            return response()->json(['status' => true, 'message' => 'Subscription Plans','data'=>$rows]);
                    
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getUserSubscriptionStatus(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $row = User::where('id',$user_id)
                    ->select('id','plan_id','is_premium')
                    ->first();
            return response()->json(['status' => true, 'message' => 'Subscription Status','data'=>$row]);
                    
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function purchasePlan(Request $request){

        try {
            $data = $request->all();
            $rules =[
                'transaction_id'      =>        'required',
                'plan_id'           =>        'required',
                'amount'           =>        'required',
                'receipt'             =>        'required',
                'payment_time'        =>        'required',
                'platform'            =>        'required|in:ANDROID,IOS'          
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $error = $this->validationHandle($validator->messages()); 
                return response()->json(['status' => false, 'message' => $error]); 
            } else {
                $userId =  JWTAuth::toUser(JWTAuth::getToken())->id;
                // echo $userId;die;
                if($request->platform=='IOS'){

                    $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION); 
                    //ENDPOINT_SANDBOX,ENDPOINT_PRODUCTION
                    $receipt = str_replace("%2B","+",$request->receipt);
                    $receiptBase64Data = $receipt;
                    try {
                        //$response = $validator->setReceiptData($receiptBase64Data)->validate();
                        $sharedSecret = env('IOS_SHARED_SECRET'); // Generated in iTunes Connect's In-App Purchase menu

                        $response = $validator->setSharedSecret($sharedSecret)->setReceiptData($receiptBase64Data)->validate(); // use setSharedSecret() if for recurring subscriptions 
                    } catch (\Exception $e) {
                        return response()->json(['status' => false, 'message' => $e->getMessage(),'data'=>[]]);
                    } 
                    if (!$response->isValid()) {
                        $usr = User::where('id',$userId)->first();
                        // $user_detail = $this->showUserDetails($usr);
                        return response()->json(['status'=>false,'message'=>__('Payment failed due to receipt is not valid.'),'data'=>[]]);
                    }else{
                        foreach ($response->getPurchases() as $purchase) {
                            $start_date = date("Y-m-d",strtotime($purchase->getPurchaseDate()));
                        }
                    } 
                }else{
                    $pathToServiceAccountJsonFile      = public_path().'/inapp_android.json';

                    $googleClient = new \Google_Client();
                    $googleClient->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
                    $googleClient->setApplicationName(env('APPLICATION_NAME'));
                    $googleClient->setAuthConfig($pathToServiceAccountJsonFile);

                    $receiptData      = stripcslashes($request->receipt);
                    $finalReceiptData = json_decode(trim($receiptData,'"'),true); 
                    $googleAndroidPublisher = new \Google_Service_AndroidPublisher($googleClient);
                    $validator = new \ReceiptValidator\GooglePlay\Validator($googleAndroidPublisher);
                    $start_date = date("Y-m-d",strtotime($finalReceiptData['purchaseTime']));
                    try {
                      $response = $validator->setPackageName($finalReceiptData['packageName'])
                          ->setProductId($finalReceiptData['productId'])
                          ->setPurchaseToken($finalReceiptData['purchaseToken'])
                          ->validateSubscription();
                    } catch (\Exception $e){
                      return response()->json(['status' => false, 'message' => $e->getMessage(),'data'=>[]]); 
                    }
                }

                // Everything goes perfect   
                
                $check = Subscription::where('user_id',$userId)->where('plan_id',$request->plan_id)->where('transaction_id',$request->get('transaction_id'))->where('status','!=','CANCEL')->exists();

                if(!$check){
                    // Create Payment
                    $srow = new Subscription();
                    $srow->user_id = $userId;
                    $srow->plan_id = $request->plan_id;
                    $srow->transaction_id = $request->transaction_id;
                    $srow->receipt = $request->receipt;
                    $srow->payment_time = date('H:i',strtotime($request->payment_time));
                    $srow->start_date = $start_date;
                    $srow->end_date = date("Y-m-d",strtotime($start_date." +6 month"));
                    $srow->platform =  $request->platform;
                    $srow->amount =  $request->amount;
                    $srow->status =  'ACTIVE';
                    $srow->save();

                    // Update in user's table

                    User::where('id',$userId)->update(['is_premium'=>'1','plan_id'=>$request->plan_id]);
                    $usr = User::where('id',$userId)->first();
                    // $user_detail = $this->showUserDetails($usr);
                    
                    return response()->json(['status'=>true,'message'=>__('Plan purchased successfully.'),'data'=>$usr]);

                }else{
                    $usr = User::where('id',$userId)->first();
                    // $user_detail = $this->showUserDetails($usr);
                    return response()->json(['status'=>true,'message'=>__('Plan already purchased.'),'data'=>$usr]);
                }

                
            }     
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(),'data'=>[]]); 
        }
    }

     public function restorePlan(Request $request){

        try {
            $data = $request->all();
            $rules =[
                'transaction_id'      =>        'required',
                'plan_id'           =>        'required',
                'amount'           =>        'required',
                'receipt'             =>        'required',
                'payment_time'        =>        'required',
                'platform'            =>        'required|in:ANDROID,IOS'          
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $error = $this->validationHandle($validator->messages()); 
                return response()->json(['status' => false, 'message' => $error]); 
            } else {
                $userId =  JWTAuth::toUser(JWTAuth::getToken())->id;
                // echo $userId;die;
                if($request->platform=='IOS'){

                    $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION); 
                    //ENDPOINT_SANDBOX,ENDPOINT_PRODUCTION
                    $receipt = str_replace("%2B","+",$request->receipt);
                    $receiptBase64Data = $receipt;
                    try {
                        //$response = $validator->setReceiptData($receiptBase64Data)->validate();
                        $sharedSecret = env('IOS_SHARED_SECRET'); // Generated in iTunes Connect's In-App Purchase menu

                        $response = $validator->setSharedSecret($sharedSecret)->setReceiptData($receiptBase64Data)->validate(); // use setSharedSecret() if for recurring subscriptions 
                    } catch (\Exception $e) {
                        return response()->json(['status' => false, 'message' => $e->getMessage(),'data'=>[]]);
                    } 
                    if (!$response->isValid()) {
                        $usr = User::where('id',$userId)->first();
                        // $user_detail = $this->showUserDetails($usr);
                        return response()->json(['status'=>false,'message'=>__('Payment failed due to receipt is not valid.'),'data'=>[]]);
                    }else{
                        foreach ($response->getPurchases() as $purchase) {
                            $start_date = date("Y-m-d",strtotime($purchase->getPurchaseDate()));
                        }
                    } 
                }else{
                    $pathToServiceAccountJsonFile      = public_path().'/inapp_android.json';

                    $googleClient = new \Google_Client();
                    $googleClient->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
                    $googleClient->setApplicationName(env('APPLICATION_NAME'));
                    $googleClient->setAuthConfig($pathToServiceAccountJsonFile);

                    $receiptData      = stripcslashes($request->receipt);
                    $finalReceiptData = json_decode(trim($receiptData,'"'),true); 
                    $googleAndroidPublisher = new \Google_Service_AndroidPublisher($googleClient);
                    $validator = new \ReceiptValidator\GooglePlay\Validator($googleAndroidPublisher);
                    $start_date = date("Y-m-d",strtotime($finalReceiptData['purchaseTime']));
                    try {
                      $response = $validator->setPackageName($finalReceiptData['packageName'])
                          ->setProductId($finalReceiptData['productId'])
                          ->setPurchaseToken($finalReceiptData['purchaseToken'])
                          ->validateSubscription();
                    } catch (\Exception $e){
                      return response()->json(['status' => false, 'message' => $e->getMessage(),'data'=>[]]); 
                    }
                }

                // Everything goes perfect   
                
                $check = subscription::where('user_id',$userId)->where('plan_id',$request->plan_id)->where('transaction_id',$request->get('transaction_id'))->where('status','!=','CANCEL')->exists();

                if(!$check){
                    // Create Payment
                    $srow = new subscription();
                    $srow->user_id = $userId;
                    $srow->plan_id = $request->plan_id;
                    $srow->transaction_id = $request->transaction_id;
                    $srow->receipt = $request->receipt;
                    $srow->payment_time = date('H:i:s',strtotime($request->payment_time));
                    $srow->start_date = $start_date;
                    $srow->end_date = date("Y-m-d",strtotime($start_date." +6 month"));
                    $srow->platform =  $request->platform;
                    $srow->amount =  $request->amount;
                    $srow->status =  'ACTIVE';
                    $srow->save();

                    // Update in user's table

                    User::where('id',$userId)->update(['is_premium'=>'1','plan_id'=>$request->plan_id]);
                    $usr = User::where('id',$userId)->first();
                    // $user_detail = $this->showUserDetails($usr);
                    
                    return response()->json(['status'=>true,'message'=>__('Plan restored successfully.'),'data'=>$usr]);

                }else{
                    $usr = User::where('id',$userId)->first();
                    // $user_detail = $this->showUserDetails($usr);
                    return response()->json(['status'=>true,'message'=>__('Plan restored successfully.'),'data'=>$usr]);
                }

                
            }     
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(),'data'=>[]]); 
        }
    }

    public function cronPaymentSuccess(Request $request){
    //dd(date('Y-m-d h:i:s'));
    $rows = subscription::where('receipt','<>',Null)->where('receipt','<>','')->whereDate('end_date', date("Y-m-d"))->get();
    // print_r($rows);die;

    if($rows){
        foreach ($rows as $row) {

             if (!empty($row->platform) && !empty($row->receipt) ) {
                $validRecord    = false;
                $receipt        = $row->receipt;
                $platform       = $row->platform;
                $transaction_id = $row->transaction_id;
                $user_id        = $row->user_id;

                if($platform && $receipt ){
                 
                    if($platform =='android' || $platform =='Android' || $platform =='ANDROID'){
                        
                        $receiptData      = stripcslashes($receipt);
                        $finalReceiptData = json_decode(trim($receiptData,'"'),true);

                        //dd($finalReceiptData);

                        if( $finalReceiptData !== null ){
                            
                            $applicationName = 'VerifyHim';
                            $conFigFile      = public_path().'/inapp_android.json';
                    
                            //dd($conFigFile);
                            
                            $googleClient = new \Google_Client();
                            $googleClient->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
                            $googleClient->setApplicationName( $applicationName);
                            $googleClient->setAuthConfig($conFigFile);

                            //dd($googleClient);

                            $packageName = $finalReceiptData['packageName'];
                            $productId = $finalReceiptData['productId'];
                            $purchaseToken = $finalReceiptData['purchaseToken'];
                            
                            $googleAndroidPublisher = new \Google_Service_AndroidPublisher($googleClient);
                            
                            $validator = new \ReceiptValidator\GooglePlay\Validator($googleAndroidPublisher);
                            try {

                                $response = $validator->setPackageName($packageName)
                                ->setProductId($productId)
                                ->setPurchaseToken($purchaseToken)
                                ->validateSubscription();

                                $paymentState = $response->getpaymentState();
                                //check valid transaction android


                                if($paymentState ==1){
                                    $validRecord = true;
                                }

                                
                            } catch (Exception $e){
                                
                               echo $e->getMessage();
                               //Log::error($e->getMessage());
                            
                            }
                        }                   

                    }

                    //for ios
                    if(($platform =='ios' || $platform =='IOS') && !empty($receipt) ){

                        //ENDPOINT_PRODUCTION
                       // $validator = new iTunesValidator(iTunesValidator::ENDPOINT_SANDBOX);

                        $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION);

                        $receiptBase64Data = $receipt;
                        try {
                            $response = $validator->setReceiptData($receiptBase64Data)->validate();
                            $sharedSecret = env('IOS_SHARED_SECRET'); // Generated in iTunes Connect's In-App Purchase menu
                            $response = $validator->setSharedSecret($sharedSecret)
                            ->setReceiptData($receiptBase64Data)
                            ->validate(); // use setSharedSecret() if for recurring subscriptions
                        } catch (Exception $e) {

                              Log::error($e->getMessage());
                        }

                        if ($response->isValid()) {
                            
                            $validRecord = true;
                        //echo 'Receipt is valid.' . PHP_EOL;
                        //echo 'Receipt data = ' . print_r($response->getReceipt()) . PHP_EOL;
                            $receipt = $response->getReceipt();
                        
                            foreach ($response->getPurchases() as $purchase) {
                                //echo 'getProductId: ' . $purchase->getProductId() . PHP_EOL;
                                //echo 'getTransactionId: ' . $purchase->getTransactionId() . PHP_EOL;
                                $checkTransactionId = (string)$purchase->getTransactionId();




                                $expire = substr($purchase->getExpiresDate()->toIso8601String(),0,10);
                                
                                $current_date = date('Y-m-d');

                                //dd("$expire $current_date");

                                $expireTimestamp = strtotime($expire); 
                                $currentTimestamp = strtotime($current_date); 

                                //dd($expireTimestamp.'  '.$currentTimestamp);

                                if($checkTransactionId !== $transaction_id || $expireTimestamp < $currentTimestamp){
                                    $validRecord = false;
                                }
                                if ($purchase->getPurchaseDate() != null) {
                                    //echo 'getPurchaseDate: ' . $purchase->getPurchaseDate()->toIso8601String() . PHP_EOL;
                                }



                                // dd($checkTransactionId);

                                if($checkTransactionId !== $transaction_id){
                                    $validRecord = false;
                                }
                                if ($purchase->getPurchaseDate() != null) {
                                // echo 'getPurchaseDate: ' . $purchase->getPurchaseDate()->toIso8601String() . PHP_EOL;die;
                                }
                            }

                        } else {
                            
                            Log::error('IOS Receipt is not valid -: '.$response->getResultCode());
                            //echo 'Receipt is not valid.' . PHP_EOL;
                            //echo 'Receipt result code = ' . $response->getResultCode() . PHP_EOL;
                        }
                        
                    }


                    //Valid transcation save
                    if($validRecord ==true){
                        // dd("aaa");
                        
                        $payment =  subscription::where('id',$row->id)->first();
                        
                       
                        $end_date = date("Y-m-d", strtotime('+1 months'));
                        
                        $payment->payment_time = date("H:i");
                        $payment->end_date = $end_date;

                        $done = $payment->save();
                        if($done){

                            $msg = 'Payment succss row ID : '.$payment->id;
                            //Log::error($msg);
                            
                        }else{
                            $msg = 'Payment failed row ID : '.$payment->id;
                            //Log::error($msg);
                        }

                        echo $msg.'<br/>';

                    }
                    else{
                        // dd("bb");
                        $payment =  subscription::where('id',$row->id)->first();
                        $payment->status = 'CANCEL';
                        $payment->save(); 
                        $done = User::where('id',$user_id)->update(['is_premium' => '0']); 
                        if($done){ 
                            $msg = 'Payment renew canceled row ID : '.$payment->id;
                            //Log::error($msg);  
                        }else{
                            $msg = 'Payment renewed canceled failed row ID : '.$payment->id;
                            //Log::error($msg);
                        } 
                        echo $msg.'<br/>'; 

                    }
                }
            }
        } //row    
    } 
    }


    public function cronExpireFreeSubscription(){

        $cur_date = date("Y-m-d");
        $rows = User::where('is_premium','1')->where('user_type','1')->select('id','created_at')->get();

        foreach($rows as $val){

            $registration_date = date("Y-m-d",strtotime($val->created_at));
            $ex_date = date('Y-m-d', strtotime("+6 months", strtotime($registration_date)));

            if(strtotime($cur_date) == strtotime($ex_date)){
                User::where('id',$val->id)->update(['is_premium'=>'0','plan_id'=>null]);
            }
        }

        die("done");
    }


    public function checkNotification(){
        PushNotification::Notify(2,'restaurant', 1, 'MATCH','Test Notification','CENA', $dic = []);
    }


}



