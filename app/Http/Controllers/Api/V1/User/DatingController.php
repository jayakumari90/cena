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
use App\Models\UserDetail;
use App\Models\FavRestaurant;
use App\Models\RestaurantVisit;
use App\Models\Order;
use App\Models\UserLikeDislike;
use App\Models\FavDish;
use App\Models\RestroDish;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\BlockedUser;


class DatingController extends Controller
{
    public function datingPreferance(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'gender' => 'required',
                    'age' => 'required',
                    'radius' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $row = UserDetail::where('user_id',$user_id)->first();
                if($row){
                    $row->user_id = $user_id;
                    $row->preferred_gender = $request->gender;
                    $row->preferred_age = $request->age;
                    $row->radius = $request->radius;
                    $row->save();
                    return response()->json(['status' => true, 'message' => 'Dating preference add succssfully', 'data' => $row]);
                }else{
                    return response()->json(['status' => false, 'message' => 'No record found.', 'data' => []]);
                }
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getUserPreference(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'lat' => 'required',
                    'lng' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $radius = Setting::select('default_miles')->first();

                //getting user preference
                $pref = UserDetail::where('user_id',$user_id)
                        ->select('preferred_gender',
                            'preferred_age',
                            'radius',
                            'user_preference',
                            DB::raw("substring_index(preferred_age,'-',1) as min_age"),
                            DB::raw("substring_index(preferred_age,'-',-1) as max_age")
                        )
                        ->whereNotNull('preferred_gender')
                        ->whereNotNull('preferred_age')
                        ->whereNotNull('radius')
                        ->first();

               if($pref){
                   
                    //liked users

                    $matched_users = UserLikeDislike::where('user_id',$user_id)
                      ->pluck('other_user_id')->toArray();


                    //blocked users

                    $blocked_users = BlockedUser::where('user_id',$user_id)
                      ->pluck('other_user_id')->toArray();
                      
                    $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(user_details.lat) ) * cos( radians(user_details.lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(user_details.lat) ) ) ,0) AS distance";

                   $base_path = asset('public/uploads/profile_picture/').'/'; 

                   //getting match preferences
                    $rows = UserDetail::
                            leftJoin('users', function($join) {
                              $join->on('user_details.user_id', '=', 'users.id');

                            })
                            ->leftJoin('fav_dishes', function($join) {
                              $join->on('fav_dishes.user_id', '=', 'users.id');

                            })
                            ->select("users.id",
                                        "users.name",
                                        "user_details.user_id",
                                        "user_details.age",
                                        "user_details.about_us",
                                        "user_details.lat",
                                        "user_details.lng",
                                        "user_details.user_preference",
                                        DB::raw('IF(profile_image IS NULL, "", CONCAT("'.$base_path.'", profile_image))  AS profile_image'),
                                         DB::raw($distanceQuery),

                            );
                            if($pref->preferred_gender == 'B')
                            {

                                $rows = $rows->where(function ($query) {
                                    $query->where('gender','M')
                                         ->orWhere('gender','F');
                                });
                            }else{
                                $rows = $rows->where('gender',$pref->preferred_gender);
                            }
                            $rows = $rows->having('distance','<=',$pref->radius)
                            ->where('age','>=',$pref->min_age)
                            ->where('age','<=',$pref->max_age)
                            ->where('user_preference','!=','Food')
                            ->where('user_details.user_id','!=',$user_id)
                            ->whereNotIn('user_details.user_id',$matched_users)
                            ->whereNotIn('user_details.user_id',$blocked_users);

                            if($request->dish_id == ''){
                                $rows = $rows->where(function ($query) {
                                        $query->where('user_preference','Dating')
                                            ->orWhere('user_preference','Both');
                                    });
                            }
                            else{
                                 $rows = $rows->where(function ($query) use ($request) {
                                        $query->where('user_preference','Both')
                                            ->where('fav_dishes.dish_id',$request->dish_id)
                                            ->where('fav_dishes.status','1');
                                    });
                            }

                            $rows = $rows->groupBy('user_details.user_id')->get();
                            // dd($rows);
                    return response()->json(['status' => true, 'message' => 'Get dating preference', 'data' => $rows]);
               }else{
                    return response()->json(['status' => false, 'message' => 'Please set your preference first.', 'data' => []]);
               }
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }


    public function matchedUsers(Request $request){

        try{
            $user_id = JWTAuth::parseToken()->authenticate()->id;
            // echo $user_id;die;
            $base_path = asset('public/uploads/profile_picture/').'/'; 
            $rows = DB::table("user_like_dislikes as y")
            ->select('users.id','users.name',\DB::raw('IF(profile_image IS NULL, "", CONCAT("'.$base_path.'", profile_image))  AS profile_image'))
            ->leftjoin('users', 'y.other_user_id', '=', 'users.id')

           
            ->whereExists(function ($query) {
                $query->select('y2.id')
                      ->from('user_like_dislikes as y2')
                      ->whereRaw('y.id <> y2.id and y.user_id = y2.other_user_id and y.other_user_id = y2.user_id');
                  })
              ->where('user_id',$user_id)->where('is_like','1')->get();
              $result_arr = array();
              foreach($rows as $res){
                $blockuser = BlockedUser::where(function ($query) use ($res,$user_id) {
                    $query->where('user_id',$user_id)
                    ->where('other_user_id',$res->id);
                })
                ->orWhere(function ($query) use ($res,$user_id) {
                    $query->where('user_id',$res->id)
                    ->where('other_user_id',$user_id);
                })
                ->first();

                if(!$blockuser){
                    $result_arr[] = $res;
                }
              }
            return response()->json(['status' => true, 'message' => 'Matching preference', 'data' => $result_arr]);

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function otherUserProfile(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'other_user_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $base_path = asset('public/uploads/profile_picture/').'/'; 
                $row = User::leftJoin('user_details', function($join) {
                          $join->on('users.id', '=', 'user_details.user_id');
                })
                ->where('users.id',$request->other_user_id)
                ->select('users.id',
                    'users.name',
                    'users.email',
                    'user_details.age',
                    'user_details.gender',
                    'user_details.about_us',
                    'user_details.user_profession',
                    'user_details.user_company',
                    \DB::raw('IF(profile_image IS NULL, "", CONCAT("'.$base_path.'", profile_image))  AS profile_image'),
                )
                ->first();
                $fav_restro = FavRestaurant::where('user_id',$request->other_user_id)
                                ->where('status','1')
                                ->with('getrestroInfo')
                                ->orderBy('id','desc')
                                ->paginate(10);

                $restro_visit = RestaurantVisit::where('user_id',$request->other_user_id)
                                ->where('status','1')
                                ->with('getrestroInfo')
                                ->orderBy('id','desc')
                                ->paginate(10);

                $restro_orders = Order::select('id','restro_id')
                                ->where('user_id',$request->other_user_id)
                                ->where('status','delivered')
                                ->with('getRestroDetail')
                                ->orderBy('id','desc')
                                ->paginate(10);

                $row->fav_restro = $fav_restro;
                $row->restro_visit = $restro_visit;
                $row->restro_orders = $restro_orders;
                if($row){

                    return response()->json(['status' => true, 'message' => 'Other user detail', 'data' => $row]);
                }else{
                    return response()->json(['status' => true, 'message' => 'User Not Found', 'data' => []]);
                }
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function likeDislikeUser(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'other_user_id' => 'required',
                    'is_like' => 'required|in:0,1',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    // echo $user_id;die;
                    $row = UserLikeDislike::where(['other_user_id'=>$request->other_user_id,
                                                'user_id'=>$user_id])->first();

                    if(!$row){
                        $row = new UserLikeDislike();
                    }
                    $row->user_id = $user_id;
                    $row->other_user_id = $request->other_user_id;
                    $row->is_like = $request->is_like;

                    if($row->save()){
                        //found match
                        $match = UserLikeDislike::where(['other_user_id'=>$user_id,'user_id'=>$request->other_user_id,'is_like'=>'1'])->first(); 

                        if($match && (string)$request->is_like == '1'){
                            if($row->getOtheruserInfo->notification_setting == '1'){
                                  $message_title = 'Match Found!';
                                  $message = 'You found a new match '.$row->getuserInfo->name.'.';
                         
                                  Notification::saveNotification($row->other_user_id,'user',$row->user_id,'MATCH',$message,$message_title);
                         
                                }


                              if($row->getuserInfo->notification_setting == '1'){
                                  $message_title = 'Match Found!';
                                  $message = 'You found a new match '.$row->getOtheruserInfo->name.'.';
                         
                                  Notification::saveNotification($row->user_id,'user',$row->other_user_id,'MATCH',$message,$message_title);
                         
                                }
                        } 
                                                

                        $msg = $request->is_like == '1' ? 'liked' : 'disliked';
                        return response()->json(['status' => true, 'message' => 'User '.$msg.' successfully.','data'=>$row]);
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

    public function updateLocation(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'lat' => 'required',
                    'lng' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $row = UserDetail::where('user_id',$user_id)->first();          
                    $row->lat = $request->lat;
                    $row->lng = $request->lng;
                    if($row->save()){
                        return response()->json(['status' => true, 'message' => 'Location updated successfully.','data'=>$row]);
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


}

