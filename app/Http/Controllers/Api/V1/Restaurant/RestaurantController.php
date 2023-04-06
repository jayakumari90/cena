<?php

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Config;
use Mail;
use JWTAuth;
use File;
use App\Models\User;
use App\Models\RestaurantDevice;
use App\Models\DishImage;
use App\Models\RestaurantCategory;
use App\Models\RestaurantDetail;
use App\Models\DishCategory;
use App\Models\RestroImage;
use App\Models\RestroDish;
use App\Models\Struggling;
use App\Models\StrugglingDoc;
use App\Models\SocialAccount;
use App\Models\UserOtp;
use App\Models\RegisterDoc;
use App\Models\WalletTransaction;
use App\Models\Notification;
use App\Models\Plan;
use App\Lib\Stripe;
use Illuminate\Support\Facades\Log;
class RestaurantController extends Controller
{
    
    public $response = ['status' => false, 'message' => "", 'data' => null];
    public $status_code = 200;

    public function dishCat(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $dishCategory = DishCategory::orderBy('id','desc')->get();
                return response()->json(['status' => true ,'message' => 'success','data' => $dishCategory]);
            }
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function restaurantCat(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $category = RestaurantCategory::orderBy('id','desc')->get();
                $struggling = Struggling::orderBy('id','desc')->get();
                $data = array('category'=>$category,'struggling'=>$struggling);
                return response()->json(['status' => true ,'message' => 'success','data' => $data]);
            }
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function checkRestaurant(Request $request)
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
                    $user = User::where(['email'=>$request->email,'user_type'=>2])->first();
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

    
    public function register(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            $base_url =  url('/');
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'category'=>'required',
                    'email' => 'required|string|email|unique:users,email',
                    'password' => 'required|min:8',
                    'license_number' => 'required',
                    'address'=>'required',
                    'lat'=>'required',
                    'lng'=>'required',
                    'device_type' => 'required',
                    'device_id' => 'required',
                    'device_uniqueid' => 'required',
                    'isdont_askon' => 'required',
                    'documents' => 'required',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }
            
                    $user = new User();
                    $user->name = $request->name;
                    $user->email = $request->email;
                    $password = Hash::make($request->input('password'));    
                    $user->password = $password;
                    $user->user_type = 2;
                    $user->status=1;
                    $user->is_admin=0;

                    //generate stripe customer id
                    $customer_token = Stripe::createCustomer($request->get('email')); 
                    // print_r($customer_token);die;
                    if($customer_token['status']==1){
                        $user->stripe_customer_id = $customer_token['customer_id'];
                    }

                  
                    if($user->save())
                    {

                     if(!empty($request->documents)){
                        $files = $request->documents;

                            foreach($files as $doc){

                                $name = time().rand(1,100).'.'.$doc->extension();
                                $responseData = $doc->move(public_path('uploads/register_docs/'), $name); 
                                if($responseData){ 
                                    $drow = new RegisterDoc();
                                    $drow->restro_id = $user->id;
                                    $drow->document = $name;
                                    $drow->save();
                                } 
                                
                            }
                        }


                        $detail = new RestaurantDetail();
                        $detail->user_id = $user->id;
                        $detail->license_number = $request->license_number;
                        $detail->category_id = $request->category;
                        $detail->address = $request->address;
                        $detail->lat = $request->lat;
                        $detail->lng = $request->lng;

                        if($request->struggling_restaurant == '1'){

                            $detail->struggling_restaurant = $request->struggling_restaurant;
                            $detail->struggling_options = $request->struggling_options;
                             if(!empty($request->struggling_documents)){
                                    $files = $request->struggling_documents;

                                    foreach($files as $doc){

                                        $name = time().rand(1,100).'.'.$doc->extension();
                                        $responseData = $doc->move(public_path('uploads/struggling_docs/'), $name); 
                                        if($responseData){ 
                                            $drow = new StrugglingDoc();
                                            $drow->restro_id = $user->id;
                                            $drow->document = $name;
                                            $drow->save();
                                        } 
                                        
                                    }
                                } 
                        }
                        
                        $detail->save();

                        
                        RestaurantDevice::manageDeviceIdAndToken($user->id,$request->device_id,$request->device_type,$request->device_uniqueid,$request->isdont_askon,'add');
                   
                        $templete = "email.signupmail";
                        $data['name'] = $user->name;
                        $data['token'] = $user->token;
                        $data['email'] = $user->email;
                        $data['base_url'] = $base_url;
                        $subject = "Welcome to cena family";
                        sendMail('email.signupmail',$data,$subject);

                        UserOtp::where('email',$user->email)->delete();
                        $restaurant = $this->showRestroDetails($user->id);
                        $restaurant->token = JWTAuth::fromUser($user); 
                                                                       
                        return response()->json(['status' => true ,'message' => __('Successfully Registered'),'data' => $restaurant]);
                    }else{
                        DB::rollBack();
                        return response()->json(['status' => false ,'message' => __('Error Occured'),'data' => []]);
                    } 
                
            }else{
                    return response()->json(['status' => false, 'message' => 'Invailid auth token']);

            }

            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function showRestroDetails($id){

        
        $restaurant = User::leftJoin('restaurant_details', function($join) {
                  $join->on('users.id', '=', 'restaurant_details.user_id');

                })->leftJoin('restaurant_categories', function($join) {
                  $join->on('restaurant_details.category_id', '=', 'restaurant_categories.id');
                })
                // ->leftjoin("strugglings",\DB::raw("FIND_IN_SET(strugglings.id,restaurant_details.struggling_options)"),">",\DB::raw("'0'"))
        ->where('users.id',$id)
        ->select('users.id',
            'users.name',
            'users.email',
            'users.user_type',
            'users.is_onboarding',
            'users.is_two_factoron',
            'users.notification_setting',
            'users.stripe_customer_id',
            'users.stripe_user_id',
            'restaurant_details.license_number',
            'restaurant_details.address',
            'restaurant_details.struggling_restaurant',
            'restaurant_details.struggling_options',
            'restaurant_details.lat',
            'restaurant_details.lng',
            'restaurant_details.category_id',
            'restaurant_details.avg_rating',
            'restaurant_details.total_reviews',
            'restaurant_categories.category_name',
            // \DB::raw("GROUP_CONCAT(strugglings.options) as struggling_options")
        )
        ->with(['getRegistergDocs','getStrugglingDocs','getRestaurantImages','getRestaurantDishes'])
        ->groupBy('restaurant_details.user_id')
        ->first();

        $stru = explode(',',$restaurant->struggling_options);
        $strugglings = Struggling::whereIn('id',$stru)->get();
        $restaurant->strugglings = $strugglings;
        return $restaurant;
    }

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

                $user = User::select('id','name','email','password','user_type','status')
                ->where(['email' => $request->get('email'), 'user_type' => 2,'is_admin'=>0])
                ->first();

                if (!$user) {
                    $this->response['message'] = "User does not exist.";
                    return response()->json($this->response, $this->status_code);
                }
                if (!Hash::check($request->get('password'), $user->password)) {
                    $this->response['message'] = "Credentials does not match.";
                    return response()->json($this->response, $this->status_code);
                }
                if ($user->status == 0) {
                    $this->response['message'] = "Your account is deactivated from admin. Please contact admin for more details.";
                    return response()->json($this->response, $this->status_code);
                }
                
                //update user device
                RestaurantDevice::manageDeviceIdAndToken($user->id,$request->device_id,$request->device_type,$request->device_uniqueid,'','add');


                //get device detail
                $device = RestaurantDevice::where(['user_id' => $user->id, 'device_id' => $request->get('device_id'),'device_uniqueid'=>$request->device_uniqueid])->first();

                //get restaurant detail
                $restaurant = $this->showRestroDetails($user->id);

                $token = JWTAuth::fromUser($user);
                $restaurant->token = $token;
                $restaurant->isdont_askon = $device->isdont_askon;
                $restaurant->device_uniqueid = $device->device_uniqueid;

                $this->response["status"] = true;
                $this->response['message'] = "Successfully Logged In";
                $this->response["data"] = $restaurant;
                return response()->json($this->response, $this->status_code);
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid Auth Token']);
            }
            
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }    

    
    
    public function twoFactorCode(Request $request){
        try{
            $base_url =url('/');
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                //DB::beginTransaction();

                $validator = Validator::make($request->all(), [
                    'is_two_factoron' => 'required|in:0,1',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }
                $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                $user = User::where('id',$user_id)->select('id','name','email','is_two_factoron')->first();
                $user->is_two_factoron = $request->is_two_factoron;
                $user->save();
                return response()->json(['status' => true, 'message' =>'success','data'=>$user]);
            }else{
                return response()->json(['status' => false, 'message' =>'Invalid auth token']);
            }
        }catch(\Throwable $th){
            $this->status_code = 500;
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);

        }
    }

    public function verifyUser(Request $request){
        try{
            $base_url =url('/');
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
                $chkDevice = RestaurantDevice::where(['user_id'=>$user_id,'device_uniqueid'=>$request->device_uniqueid])->first();
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

                if ($validator->fails()) {
                    $response['status'] = false;
                    $response['message'] = $validator->errors()->first();
                    $response['data'] = [];
                    return $response;
                } else {
                    $users = User::where(['email'=> $data['email'],'user_type'=>2])->first();

                    if ($users) {
                        if ($users->status == 1) {
                            $name = $users->name;
                            $email = $users->email;

                            // generate random token insert in user table & send with url.
                            $hash = md5($email) . time();
                            $addotp = UserOtp::create([
                                            'email'=>$request->email,
                                            // 'user_id'=>$users->id,
                                            'otp_code'=>$otp
                                        ]);
                            /*$users->otp = $otp;
                            $users->save();*/

                            $data = [
                                'email' => $users->email,
                                'name' => $users->name,
                                'base_url' => $base_url,
                                'otp' => $otp
                            ];
                            //Send email
                            $email_subject = 'Reset Password';
                            sendMail('email.forgotPassmail',$data,$email_subject);
                            

                            return response()->json(['status' => true, 'message' => 'Otp for reset password has been sent to your mail. Check your inbox.', 'data' => [],'otp'=>$otp]);
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
                            ->where('user_type',2)
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

    public function onboardRestaurant(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'restro_images.*' => 'required',
                    'dishes' => 'required',
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    // dd($request->all());
                     Log::info($request->all());
                    //add restro images
                     User::where('id',$user_id)->update(['is_onboarding'=>'1']);
                     if($request->hasFile('restro_images')){
                        //firstly delete image
                        $images = RestroImage::where('restro_id',$user_id)->get();
                        foreach ($images as $image) {
                           $image_path = public_path().'/uploads/restro_images/'.$image->image;
                           $thumb_image_path = public_path().'/uploads/restro_images/'.$image->thumb;
                            if(File::exists($image_path)) {
                                File::delete($image_path);
                               }

                             if(File::exists($thumb_image_path)) {
                                File::delete($thumb_image_path);
                               }
                        }
                        RestroImage::where('restro_id',$user_id)->delete();
                        $files = $request->restro_images;

                        foreach($files as $images){

                            $destinationPath = 'uploads/restro_images/';
                            $responseData = doUpload($images,$destinationPath,true);    
                            if($responseData['status']=="true"){ 
                                $pimg = new RestroImage();

                                $pimg->restro_id = $user_id;
                                $pimg->image = $responseData['file'];
                                $pimg->thumb = $responseData['thumb'];
                                $pimg->save();
                            } 
                            
                        }
                    }

                    //add dishes
                    if($request->dishes){
                        // RestroDish::where('restro_id',$user_id)->delete();
                        $dish = json_decode($request->dishes,true);
                        // print_r($dish);die;

                        foreach($dish as $key => $row){
                            $dish = new RestroDish;
                            $dish->restro_id = $user_id;
                            $dish->category_id = $row['dish_category'];
                            $dish->name = $row['dish_name'];
                            $dish->price = $row['price'];
                            $dish->description =$row['description'];
                            $dish->is_popular =$row['is_popular'];
                            $dish->discount = $row['discount'];
                            if($dish->save()){
                                $img_key = 'dish'.$key;
                                 if($request->$img_key && $request->hasFile($img_key)){
                                    DishImage::where('dish_id',$dish->id)->delete();
                                    $filess = $request->$img_key;
                                    
                                    /*$files = $row['dish_images[]'];*/
                                    foreach($filess as $images){

                                        $destinationPath = 'uploads/dish_images/';
                                        $responseData = doUpload($images,$destinationPath,true);    
                                        if($responseData['status']=="true"){ 
                                            $pimg = new DishImage();

                                            $pimg->dish_id = $dish->id;
                                            $pimg->image = $responseData['file'];
                                            $pimg->thumb = $responseData['thumb'];
                                            $pimg->save();
                                        } 
                                        
                                    }
                                }//end if
                            }// end row save

                        }//end dish loop
                    }//end check dish if
                    $user = $this->showRestroDetails($user_id);
                    return response()->json(['status' => true, 'message' => 'Onboarding Successful.','data'=>$user]);
                }

            }else{
               // die('no');
                return response()->json(['status' => true, 'message' => 'Invailid Otp', 'data' => $user]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    
    public function getProfile(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                $user = $this->showRestroDetails($user_id);
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

    public function editProfile(Request $request)
    {
        try {
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $base_url =  url('/');
                $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'category' => 'required',
                    'license_number' => 'required',
                    'address' => 'required'
                ]);

                if ($validator->fails()) {
                    $this->response['message'] = $validator->errors()->first();
                    return response()->json($this->response, $this->status_code);
                } else {
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $users = User::whereId($user_id)->first();
                    if ($users) {
                     
                        $users->name = (isset($request->name)) ? $request->name : $users->name;
                        $users->save();

                        $detail = RestaurantDetail::where('user_id',$users->id)->first();
                        $detail->license_number = $request->license_number;
                        $detail->category_id = $request->category;
                        $detail->address = $request->address;
                        $detail->lat = $request->lat;
                        $detail->lng = $request->lng;
                         if($request->struggling_restaurant == '1'){
                            $detail->struggling_restaurant = $request->struggling_restaurant;
                            $detail->struggling_options = $request->struggling_options;
                            
                             if(!empty($request->struggling_documents)){

                            //firstly delete doc
                                $docs = StrugglingDoc::where('restro_id',$user_id)->get();
                                foreach ($docs as $doc) {
                                   $doc_path = public_path().'/uploads/struggling_docs/'.$doc->document;
                                   
                                    if(File::exists($doc_path)) {
                                        File::delete($doc_path);
                                       }

                                }

                                StrugglingDoc::where('restro_id',$user_id)->delete();
                                $files = $request->struggling_documents;

                                foreach($files as $doc){

                                    $name = time().rand(1,100).'.'.$doc->extension();
                                    $responseData = $doc->move(public_path('uploads/struggling_docs/'), $name); 
                                    if($responseData){ 
                                        $drow = new StrugglingDoc();
                                        $drow->restro_id = $user_id;
                                        $drow->document = $name;
                                        $drow->save();
                                    } 
                                    
                                }
                            } 
                        }

                        $detail->save();

                        if($request->hasFile('restro_images')){
                            //firstly delete image
                            $images = RestroImage::where('restro_id',$user_id)->get();
                            foreach ($images as $image) {
                               $image_path = public_path().'/uploads/restro_images/'.$image->image;
                               $thumb_image_path = public_path().'/uploads/restro_images/'.$image->thumb;
                                if(File::exists($image_path)) {
                                    File::delete($image_path);
                                   }

                                 if(File::exists($thumb_image_path)) {
                                    File::delete($thumb_image_path);
                                   }
                            }
                            RestroImage::where('restro_id',$user_id)->delete();

                            $files = $request->restro_images;

                            foreach($files as $images){

                                $destinationPath = 'uploads/restro_images/';
                                $responseData = doUpload($images,$destinationPath,true);    
                                if($responseData['status']=="true"){ 
                                    $pimg = new RestroImage();

                                    $pimg->restro_id = $user_id;
                                    $pimg->image = $responseData['file'];
                                    $pimg->thumb = $responseData['thumb'];
                                    $pimg->save();
                                } 
                                
                            }
                        }
                        $user = $this->showRestroDetails($user_id);
                    
                        return response()->json(['status' => true, 'message' => 'Profile updated successfully.', 'data' => $user]);
                       
                        
                    } else {
                        return response()->json(['status' => false, 'message' => 'User is not registered with us. Please Register.', 'data' => []]);
                    }
                }
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }
    public function logout(Request $request)
    {
        $headers = apache_request_headers();
        $chkToken = chkAuthToken($headers['auth_token']);
        if($chkToken == 1){
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $userDevice = RestaurantDevice::where(['user_id'=>$user_id,'device_uniqueid'=>$request->device_uniqueid])->update(['device_id'=>null,'device_type'=>null]);
                   
            JWTAuth::invalidate();
            $this->response['status'] = true;
            $this->response['message'] = "Successfully Logged Out.";
            $this->status_code = 200;
            return response()->json($this->response, $this->status_code);
        }else{

            return response()->json(['status' => false, 'message' => 'Invailid auth token']);
        }
    }

    public function notificationSetting(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'status' => 'required|in:0,1',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $row = User::where('id',$user_id)->first();
                    $row->notification_setting = (string)$request->status;
                    if($row->save()){
                        return response()->json(['status' => true, 'message' => 'Notification setting updated successfully.','data'=>$row]);
                    }else{
                        return response()->json(['status' => false, 'message' => 'Error Occured.']);
                    }
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function stripeConnection()
    {
        $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

        $user = User::where('id',$user_id)->first();

        if($user->stripe_user_id=='' && $user->stripe_user_id==null){
              $client_id = Config::get('constants.CLIENT_ID');
              $state = $user->stripe_customer_id;
              $email = $user->email;
              $redirect_uri = url(route('create-connect'));
              $name = $user->name;
              $url = "https://connect.stripe.com/express/oauth/authorize?redirect_uri=$redirect_uri&client_id=$client_id&state=$state&stripe_user[email]=$email&suggested_capabilities[]=card_payments&scope=read_write&always_prompt=true&stripe_user[name]=$name";

              return response()->json(['status' => 'true','url'=>$url]);
          }else{
            return response()->json(['status' => 'false','url'=>'','message'=>'You are already connected to stripe.']);
          }
    }

    public function walletToBankTransfer(Request $request){
        $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

        $user = User::select('id','stripe_user_id')->where('id',$user_id)->first();
        if($user->stripe_user_id!='' && $user->stripe_user_id!=null){
            $transfer_amount = Stripe::transfer($request->amount,$user->stripe_user_id);
       
            if($transfer_amount['status'] == 1){
                //amount debit in wallet
                $wallet = new WalletTransaction();
                $wallet->user_id = $user->id;
                $wallet->description = 'Amount Withdrawl';
                $wallet->amount = $request->amount;
                $wallet->save();


                if((float)$user->wallet_amount >= $request->amount){
                  $user->wallet_amount = (float)$user->wallet_amount - $request->amount;
                }else{
                    $user->wallet_amount = '0';
                }
                $user->save();
                return response()->json(['status' => true,'message'=>'Money Transferred Successfully.']);

            }
            else{
              return response()->json(['status' => false,'message'=>$transfer_amount['message']]);
            }
        }else{
            return response()->json(['status' => false,'message'=>'Please connect your stripe account first.']);
        }        
    }


    public function getWalletHistory(Request $request)
    {
        try
        {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $rows = WalletTransaction::where('user_id',$user_id)
            ->orderBy('id','desc')
            ->paginate(10);

            return response()->json(['status' => true, 'message' => 'Wallet Transactions','data'=>$rows]);
        }
        catch(\Exception $e){
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function myWallet()
    {
        try
        {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $row = User::select('id','wallet_amount','stripe_user_id')->where('id',$user_id)->first();

            return response()->json(['status' => true, 'message' => 'My Wallet','data'=>$row]);
        }
        catch(\Exception $e){
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getSubscriptionPlans(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $rows = Plan::where('plan_type','Restaurant')->get();
            return response()->json(['status' => true, 'message' => 'Subscription Plans','data'=>$rows]);
                    
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function notificationListing()
    {
        try
        {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $rows = Notification::where('user_id',$user_id)->orderBy('id','desc')->paginate(10);

            return response()->json(['status' => true, 'message' => 'Notification Listing','data'=>$rows]);
        }
        catch(\Exception $e){
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
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

            $device = RestaurantDevice::where('user_id',$user_id)->where('device_id',$request->device_id)->first();
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

}


