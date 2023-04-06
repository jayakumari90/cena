<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\User;
use App\Models\RestaurantDetail;
use App\Models\FavRestaurant;
use App\Models\FavDish;
use App\Models\RestaurantVisit;
use App\Models\RestroDish;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class UserRestroController extends Controller
{

    public function getTaxCommission(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $row = Setting::select('tax','commission')->first();
            return response()->json(['status' => true, 'message' => 'Tax & Commission','data'=>$row]);
                    
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function showHomeRestaurant(Request $request)
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

                    $result_array = array();
                    $radius = Setting::select('default_miles')->first();

                    //To search by miles instead of kilometers, replace 6371 with 3959.

                    $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(lat) ) * cos( radians(lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(lat) ) ) ,0) AS distance";

                    $nearby_restro = RestaurantDetail::
                            leftJoin('users', function($join) {
                              $join->on('restaurant_details.user_id', '=', 'users.id');

                            })
                            ->select("users.id",
                                        "users.name",
                                        "users.is_approved",
                                        "restaurant_details.address",
                                        "restaurant_details.total_reviews",
                                         "restaurant_details.review_people_count",
                                         "restaurant_details.avg_rating",
                                         DB::raw($distanceQuery)
                            )
                            ->where('users.is_approved','1')
                            ->having('distance','<',$radius->default_miles)
                            ->with(['getRestroImage'])
                            ->limit(5)
                           ->get();

                    $top_fav_restro = RestaurantDetail::
                            leftJoin('users', function($join) {
                              $join->on('restaurant_details.user_id', '=', 'users.id');
                            })
                            ->select("users.id","users.name",DB::raw($distanceQuery))
                            ->with(['getRestroImage'])
                            ->where('total_fav_count','!=',0)
                             ->where('users.is_approved','1')
                             ->having('distance','<',$radius->default_miles)
                            ->orderBy('total_fav_count','Desc')
                            ->limit(5)
                           ->get();

                    $struggling_restro = RestaurantDetail::
                            leftJoin('users', function($join) {
                              $join->on('restaurant_details.user_id', '=', 'users.id');
                            })
                            ->select("users.id","users.name",DB::raw($distanceQuery))
                            ->where('struggling_restaurant','1')
                            ->with(['getRestroImage','getStrugglingDocs'])
                             ->where('users.is_approved','1')
                             ->having('distance','<',$radius->default_miles)
                            ->orderBy('restaurant_details.id','Desc')
                            ->limit(5)
                           ->get();

                    $more_restro = RestaurantDetail::
                            leftJoin('users', function($join) {
                              $join->on('restaurant_details.user_id', '=', 'users.id');
                            })
                            ->select("users.id","users.name",DB::raw($distanceQuery))
                            ->where('struggling_restaurant','!=','1')
                            ->where('total_fav_count',0)
                            ->with(['getRestroImage','getStrugglingDocs'])
                             ->where('users.is_approved','1')
                             ->having('distance','<',$radius->default_miles)
                            ->orderBy('restaurant_details.id','Desc')
                            ->limit(5)
                           ->get();
                    $result_array = [
                        'nearby_restro'=>$nearby_restro,
                        'top_fav_restro'=>$top_fav_restro,
                        'struggling_restro'=>$struggling_restro,
                        'more_restro'=>$more_restro,
                    ]; 
                    return response()->json(['status' => true, 'message' => 'Restaurant listing','data'=>$result_array]);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function showAllRestaurant(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'type' => 'required|in:top_restro,struggling,more',
                    'lat' => 'required',
                    'lng' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(lat) ) * cos( radians(lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(lat) ) ) ,0) AS miles";

                    $radius = Setting::select('default_miles')->first();
                    if($request->type == 'top_restro'){

                        $rows = RestaurantDetail::
                            leftJoin('users', function($join) {
                              $join->on('restaurant_details.user_id', '=', 'users.id');
                            });
                            if($request->rating_filter){
                                $rows = $rows->where('avg_rating','>=',$request->rating_filter);
                            }
                            if($request->search_filter){
                                $rows = $rows->where('users.name', 'like', '%' . $request->search_filter . '%');
                            }
                            $rows = $rows->select("users.id",
                                        "users.name",
                                        "users.is_approved",
                                        "restaurant_details.address",
                                        "restaurant_details.total_reviews",
                                         "restaurant_details.review_people_count",
                                         "restaurant_details.avg_rating",
                                         DB::raw($distanceQuery)
                            )
                            ->with(['getRestroImage'])
                             ->where('users.is_approved','1')
                            ->where('total_fav_count','!=',0)
                            ->having('miles','<',$radius->default_miles)
                            ->orderBy('total_fav_count','Desc')
                           ->paginate(10);
                    }
                    if($request->type == 'struggling'){
                        $rows = RestaurantDetail::
                            leftJoin('users', function($join) {
                              $join->on('restaurant_details.user_id', '=', 'users.id');
                            });
                            if($request->rating_filter){
                                $rows = $rows->where('avg_rating','>=',$request->rating_filter);
                            }
                             if($request->search_filter){
                                $rows = $rows->where('users.name', 'like', '%' . $request->search_filter . '%');
                            }
                            $rows = $rows->select("users.id",
                                        "users.name",
                                        "users.is_approved",
                                        "restaurant_details.address",
                                        "restaurant_details.total_reviews",
                                         "restaurant_details.review_people_count",
                                         "restaurant_details.avg_rating",
                                         DB::raw($distanceQuery)
                            )
                            ->where('struggling_restaurant','1')
                             ->where('users.is_approved','1')
                             ->having('miles','<',$radius->default_miles)
                            ->with(['getRestroImage','getStrugglingDocs'])
                            ->orderBy('id','Desc')
                            ->limit(5)
                            ->paginate(10);
                       }

                       if($request->type == 'more'){

                        $rows = RestaurantDetail::
                            leftJoin('users', function($join) {
                              $join->on('restaurant_details.user_id', '=', 'users.id');
                            });
                            if($request->rating_filter){
                                $rows = $rows->where('avg_rating','>=',$request->rating_filter);
                            }
                            if($request->search_filter){
                                $rows = $rows->where('users.name', 'like', '%' . $request->search_filter . '%');
                            }
                            $rows = $rows->select("users.id",
                                        "users.name",
                                        "users.is_approved",
                                        "restaurant_details.address",
                                        "restaurant_details.total_reviews",
                                         "restaurant_details.review_people_count",
                                         "restaurant_details.avg_rating",
                                         DB::raw($distanceQuery)
                            )
                            ->with(['getRestroImage'])
                             ->where('users.is_approved','1')
                             ->having('miles','<',$radius->default_miles)
                            ->where('struggling_restaurant','!=','1')
                            ->where('total_fav_count',0)
                            ->orderBy('id','Desc')
                           ->paginate(10);
                    }
                     
                    return response()->json(['status' => true, 'message' => 'Restaurant listing','data'=>$rows]);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function restaurantDetail(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'restro_id' => 'required',
                    'lat' => 'required',
                    'lng' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $headers = apache_request_headers();
                    // Log::info($headers);
                    $token = $request->bearerToken();
                    $user_id = '';
                    if($token){
                        $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    } 
                    $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(restaurant_details.lat) ) * cos( radians(restaurant_details.lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(restaurant_details.lat) ) ) ,0) AS miles";


                    $restaurant = User::leftJoin('restaurant_details', function($join) {
                      $join->on('users.id', '=', 'restaurant_details.user_id');

                    })
                    ->where('users.id',$request->restro_id)
                    ->select('users.id',
                        'users.name',
                        'users.is_approved',
                        'restaurant_details.address',
                        'restaurant_details.total_reviews',
                        'restaurant_details.avg_rating',
                        'restaurant_details.review_people_count',
                        DB::raw($distanceQuery),
                        DB::raw("if((SELECT count(*) FROM `restaurant_visits` WHERE  restaurant_visits.user_id = '".$user_id."' AND restaurant_visits.status = '1' AND restaurant_visits.restro_id=users.id  )>0,true,false) as is_visit"),
                        DB::raw("if((SELECT count(*) FROM `fav_restaurants` WHERE  fav_restaurants.user_id = '".$user_id."' AND fav_restaurants.status = '1' AND fav_restaurants.restro_id=users.id  )>0,true,false) as is_fav_restro"),
                    )
                    ->with(['getOneRestaurantImage'])
                    ->first();

                    $restro_dishes = RestroDish::where('restro_id',$request->restro_id)
                                    ->where('is_deleted','!=','1')
                                    ->where('is_active','1')
                                    ->select("restro_dishes.id","restro_dishes.name",
                                            "restro_dishes.price","restro_dishes.description",
                                            "restro_dishes.discount","restro_dishes.is_popular",
                                        DB::raw("if((SELECT count(*) FROM `fav_dishes` WHERE  fav_dishes.user_id = '".$user_id."' AND fav_dishes.status = '1' AND fav_dishes.dish_id=restro_dishes.id  )>0,true,false) as is_fav"),
                                        DB::raw("(SELECT count(*) FROM `fav_dishes` WHERE fav_dishes.status = '1' AND fav_dishes.dish_id=restro_dishes.id) as total_liked_count"))
                                    ->with(['getLikedUsers','getDishImages'])
                                    ->paginate(10);

                    $restaurant->restro_dishes = $restro_dishes;
                     
                    return response()->json(['status' => true, 'message' => 'Restaurant Detail','data'=>$restaurant]);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function restaurantDetailWithDishCategory(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'restro_id' => 'required',
                    'lat' => 'required',
                    'lng' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(restaurant_details.lat) ) * cos( radians(restaurant_details.lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(restaurant_details.lat) ) ) ,0) AS miles";


                    $restaurant = User::leftJoin('restaurant_details', function($join) {
                      $join->on('users.id', '=', 'restaurant_details.user_id');

                    })
                    ->where('users.id',$request->restro_id)
                    ->select('users.id',
                        'users.name',
                        'users.is_approved',
                        'restaurant_details.address',
                        'restaurant_details.total_reviews',
                        'restaurant_details.avg_rating',
                        'restaurant_details.review_people_count',
                        DB::raw($distanceQuery),
                        DB::raw("if((SELECT count(*) FROM `restaurant_visits` WHERE  restaurant_visits.user_id = '".$user_id."' AND restaurant_visits.status = '1' AND restaurant_visits.restro_id=users.id  )>0,true,false) as is_visit"),
                        DB::raw("if((SELECT count(*) FROM `fav_restaurants` WHERE  fav_restaurants.user_id = '".$user_id."' AND fav_restaurants.status = '1' AND fav_restaurants.restro_id=users.id  )>0,true,false) as is_fav_restro"),
                    )
                    ->with(['getOneRestaurantImage'])
                    ->first();

                    $popular_dishes = RestroDish::where('restro_id',$request->restro_id)
                                    ->where('is_deleted','!=','1')
                                    ->where('is_active','1')
                                    ->where('is_popular','1');

                                    if($request->dish_category_id){
                                      $popular_dishes =  $popular_dishes->where('category_id',$request->dish_category_id);
                                    }
                                    $popular_dishes =  $popular_dishes->with(['getDishImages'])
                                    ->limit(5)
                                    ->get();

                    $other_dishes = RestroDish::where('restro_id',$request->restro_id)
                                    ->where('is_deleted','!=','1')
                                    ->where('is_active','1')
                                    ->where('is_popular','!=','1');
                                    if($request->dish_category_id){
                                      $other_dishes =  $other_dishes->where('category_id',$request->dish_category_id);
                                    }
                                    $other_dishes =  $other_dishes->with(['getDishImages'])
                                    ->limit(5)
                                    ->get();


                    $restaurant->popular_dishes = $popular_dishes;
                    $restaurant->other_dishes = $other_dishes;
                     
                    return response()->json(['status' => true, 'message' => 'Restaurant Detail','data'=>$restaurant]);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
    }

    public function menuItems(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'restro_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $rows = RestroDish::where('restro_id',$request->restro_id)
                            ->where('is_active','1')
                            ->where('is_deleted','!=','1')
                            ->select('id','category_id')
                            ->with('getcategory')
                            ->groupBy('category_id')
                            ->get();

                    return response()->json(['status' => true, 'message' => 'Menu Items','data'=>$rows]);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function menuDishes(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'dish_category_id' => 'required',
                    'restro_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $rows = RestroDish::where('restro_id',$request->restro_id)
                            ->where('category_id',$request->dish_category_id)
                            ->where('is_active','1')
                            ->where('is_deleted','!=','1')
                            ->with('getDishImages','getcategory')
                            ->paginate(10);

                    return response()->json(['status' => true, 'message' => 'Menu Dishes','data'=>$rows]);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getAllDishesWithPopularity(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'restro_id' => 'required',
                    'type' => 'required|in:popular,other',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(restaurant_details.lat) ) * cos( radians(restaurant_details.lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(restaurant_details.lat) ) ) ,0) AS miles";


                    $restaurant = User::leftJoin('restaurant_details', function($join) {
                      $join->on('users.id', '=', 'restaurant_details.user_id');

                    })
                    ->where('users.id',$request->restro_id)
                    ->select('users.id',
                        'users.name',
                        'users.is_approved',
                        'restaurant_details.address',
                        'restaurant_details.total_reviews',
                        'restaurant_details.avg_rating',
                        'restaurant_details.review_people_count',
                        DB::raw($distanceQuery),
                        DB::raw("if((SELECT count(*) FROM `restaurant_visits` WHERE  restaurant_visits.user_id = '".$user_id."' AND restaurant_visits.status = '1' AND restaurant_visits.restro_id=users.id  )>0,true,false) as is_visit"),
                        DB::raw("if((SELECT count(*) FROM `fav_restaurants` WHERE  fav_restaurants.user_id = '".$user_id."' AND fav_restaurants.status = '1' AND fav_restaurants.restro_id=users.id  )>0,true,false) as is_fav_restro"),
                    )
                    ->with(['getOneRestaurantImage'])
                    ->first();
                    if($request->type == 'popular'){
                        $dishes = RestroDish::where('restro_id',$request->restro_id)
                                    ->where('is_deleted','!=','1')
                                    ->where('is_active','1')
                                    ->where('is_popular','1');
                                    if($request->dish_category_id){
                                      $dishes =  $dishes->where('category_id',$request->dish_category_id);
                                    }
                                    $dishes =  $dishes->with(['getDishImages'])
                                    ->paginate(10);
                    }
                    else{
                        $dishes = RestroDish::where('restro_id',$request->restro_id)
                                    ->where('is_deleted','!=','1')
                                    ->where('is_active','1')
                                    ->where('is_popular','!=','1');
                                    if($request->dish_category_id){
                                      $dishes =  $dishes->where('category_id',$request->dish_category_id);
                                    }
                                    $dishes =  $dishes->with(['getDishImages'])
                                    ->paginate(10);
                    }
                    $restaurant->dishes = $dishes;
                     
                    return response()->json(['status' => true, 'message' => 'Restaurant Detail','data'=>$restaurant]);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function markFavRestaurant(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'restro_id' => 'required',
                    'status' => 'required|in:0,1',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $msg = $request->status == '1' ? 'favorite.' : 'unfavorite.';
                    $row = FavRestaurant::where(['restro_id'=>$request->restro_id,
                                                'user_id'=>$user_id])
                            ->first();
                    if(!$row && (string)$request->status == '1'){
                        $row = new FavRestaurant();
                    }
                    if($row && (string)$request->status == '0'){
                       $row->delete();
                        return response()->json(['status' => true, 'message' => 'Restaurant marked '.$msg,'data'=>[]]);
                    }

                    $row->user_id = $user_id;
                    $row->restro_id = $request->restro_id;
                    $row->status = $request->status;
                    if($row->save()){
                        //update restro fav count
                        $res = RestaurantDetail::where('user_id',$request->restro_id)->first();
                        if($res){
                            if((string)$request->status == 1 ){
                            $res->total_fav_count += 1;
                            $res->save();
                            }else{
                                if($res->total_fav_count>0){
                                    $res->total_fav_count -= 1;
                                }else{
                                    $res->total_fav_count = 0;
                                }
                                $res->save();
                            }
                        } 
                        return response()->json(['status' => true, 'message' => 'Restaurant marked '.$msg,'data'=>$row]);                       
                    }
                    
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getFavRestaurant(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(lat) ) * cos( radians(lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(lat) ) ) ,0) AS miles";
            $rows = FavRestaurant::where('user_id',$user_id)
                    ->with(['getrestroInfo'=>function($q) use($distanceQuery){
                        $q->with(['getRestaurantOtherDetail'=>function($q) use($distanceQuery){
                            $q->select('id','user_id','total_reviews','review_people_count','avg_rating',\DB::raw($distanceQuery));
                        }]);
                    }])
                    ->paginate(5);
            return response()->json(['status' => true, 'message' => 'Favorite restaurants','data'=>$rows]);
                    
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => $rows]);
        }
    }

    public function markFavDish(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'dish_id' => 'required',
                    'status' => 'required|in:0,1',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $msg = $request->status == '1' ? 'favorite.' : 'unfavorite.';
                     $row = FavDish::where(['dish_id'=>$request->dish_id,
                                                'user_id'=>$user_id])
                            ->first();
                    if(!$row && (string)$request->status == '1'){
                        $row = new FavDish();
                    }

                    if($row && (string)$request->status == '0'){
                       $row->delete();
                       $data = (object) []; 
                        return response()->json(['status' => true, 'message' => 'Dish marked '.$msg,'data'=>$data]);
                    }
                    $row->user_id = $user_id;
                    $row->dish_id = $request->dish_id;
                    $row->status = $request->status;
                    if($row->save()){
                        //update dish fav count
                        $res = RestroDish::where('id',$request->dish_id)->first();
                        if($res){
                            if((string)$request->status == 1 ){
                            $res->total_fav_count += 1;
                            $res->save();
                            }else{
                                if($res->total_fav_count>0){
                                    $res->total_fav_count -= 1;
                                }else{
                                    $res->total_fav_count = 0;
                                }
                                $res->save();
                            }
                        }
                         return response()->json(['status' => true, 'message' => 'Dish marked '.$msg,'data'=>$row]);    
                    }                       
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getFavDish(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $rows = FavDish::where('user_id',$user_id)->with('getDishInfo')->paginate(5);
            return response()->json(['status' => true, 'message' => 'Favorite dishes','data'=>$rows]);      
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => $rows]);
        }
    }

    public function markVisitRestaurant(Request $request)
    {
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'restro_id' => 'required',
                    'status' => 'required|in:0,1',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
                    $msg = $request->status == '1' ? ' visit.' : ' unvisit.';
                    $row = RestaurantVisit::where(['restro_id'=>$request->restro_id,
                                                'user_id'=>$user_id])
                            ->first();

                    if($row && (string)$request->status == '0'){
                        $row->delete();
                        return response()->json(['status' => true, 'message' => 'Restaurant marked'.$msg,'data'=>[]]);
                    }

                    if(!$row && (string)$request->status == '1'){
                        $row = new RestaurantVisit();
                    }
                    $row->user_id = $user_id;
                    $row->restro_id = $request->restro_id;
                    $row->status = $request->status;
                    if($row->save()){
                        return response()->json(['status' => true, 'message' => 'Restaurant marked'.$msg,'data'=>$row]);
                    }
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getVisitRestaurant(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

            $distanceQuery = "truncate( 3959 * acos( cos( radians(" . $request->lat . ") ) * cos( radians(lat) ) * cos( radians(lng) - radians(" . $request->lng . ") ) + sin( radians(" . $request->lat . ") ) * sin( radians(lat) ) ) ,0) AS miles";

            $rows = RestaurantVisit::where('user_id',$user_id)
                    ->with(['getrestroInfo'=>function($q) use($distanceQuery){
                        $q->with(['getRestaurantOtherDetail'=>function($q) use($distanceQuery){
                            $q->select('id','user_id','total_reviews','review_people_count','avg_rating',\DB::raw($distanceQuery));
                        }]);
                    }])
                    ->paginate(5);
            return response()->json(['status' => true, 'message' => 'Restaurant Visits','data'=>$rows]);      
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => $rows]);
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

    //order apis
    public function addToCart(Request $request)
      {
        try {
          $data = $request->all();
          $validator = Validator::make(
            $data,
            [
              'dish_id' => 'required',
              'restro_id' => 'required',
              'quantity' => 'required',
              'original_price' => 'required',
              'discount' => 'required',
              'best_price' => 'required',
            ]
          );
          if ($validator->fails()) {
            $error = $this->validationHandle($validator->messages());
            return response()->json(['status' => false, 'message' => $error]);
          } else {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

            $row = Cart::where('user_id', $user_id)->first();
            if (!$row) {
              $row = new Cart();
              $row->user_id = $user_id;
              $row->save();
            }

            $chk_same_restro = CartItem::where('cart_id', $row->id)->where('restro_id', $request->restro_id)->first();
            $item_count = CartItem::where('cart_id', $row->id)->count();
            if($item_count == 0 || ($chk_same_restro && $chk_same_restro->restro_id == $request->restro_id)){
                $cart_item = CartItem::where('cart_id', $row->id)->where('dish_id', $request->dish_id)->first();
                if (!$cart_item) {
                  $cart_item = new CartItem();
                  $cart_item->quantity = $request->quantity;
                } else {
                  $cart_item->quantity = $cart_item->quantity + $request->quantity;
                }
                $cart_item->cart_id = $row->id;
                $cart_item->dish_id = $request->dish_id;
                $cart_item->restro_id = $request->restro_id;
                $cart_item->original_price = $request->original_price;
                $cart_item->discount = $request->discount;
                $cart_item->best_price = $request->best_price;
                $cart_item->save();

                $total_item = CartItem::where('cart_id', $row->id)
                  ->select(DB::raw('sum(best_price * quantity) as total_price'))
                  ->first();
                Cart::where('user_id', $user_id)->update(['cart_amount' => $total_item->total_price]);

                return response()->json(['status' => true, 'message' => 'Dish added to cart successfully.','data'=>[]]);
            }else{
                 return response()->json(['status' => false, 'message' => 'You cannot add to cart different restaurants.','data'=>[]]);
            }

          }//end else
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function updateCart(Request $request)
      {
        try {
          $data = $request->all();
          $validator = Validator::make(
            $data,
            [

              'dish_id' => 'required',
              'cart_id' => 'required',
              'quantity' => 'required',
              'original_price' => 'required',
              'discount' => 'required',
              'best_price' => 'required',
            ]
          );
          if ($validator->fails()) {
            $error = $this->validationHandle($validator->messages());
            return response()->json(['status' => false, 'message' => $error]);
          } else {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            if ($request->quantity != 0) {
              $cart_item = CartItem::where('cart_id', $request->cart_id)->where('dish_id', $request->dish_id)->first();
              $cart_item->quantity = $request->quantity;
              $cart_item->original_price = $request->original_price;
              $cart_item->discount = $request->discount;
              $cart_item->best_price = $request->best_price;
              $cart_item->save();

            } else {
              CartItem::where('cart_id', $request->cart_id)->where('dish_id', $request->dish_id)->delete();
            }

            //updating user cart total amount
            $total_item = CartItem::where('cart_id', $request->cart_id)
                ->select(DB::raw('sum(best_price * quantity) as total_price'))
                ->first();
              Cart::where('user_id', $user_id)->update(['cart_amount' => $total_item->total_price]);
            return response()->json(['status' => true, 'message' => 'Cart updated successfully.','data'=>[]]);
          }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function getCartItem(Request $request)
      {
        try {
          $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

          $rows = Cart::where('user_id', $user_id)
                    ->with('getCartItems')
                    ->first();

          return response()->json(['status' => true, 'message' => 'Your Cart Item','data'=>$rows]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function getCartCount()
      {
        try {

          $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
          $cart = Cart::where('user_id', $user_id)->first();
          if ($cart) {
            $cart_items_count = CartItem::where('cart_id', $cart->id)->sum('quantity');
            return response()->json(['status' => true, 'message' => 'Your Cart Count','data'=>$cart_items_count]);

          } else {
            return response()->json(['status' => true, 'message' => 'Your Cart Count','data'=>0]);
          }

          return response()->json($this->response, $this->status_code);
        } catch (\Exception $e) {
           return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }
    
      public function checkout(Request $request)
      {
        try {
          $data = $request->all();
          $validator = Validator::make(
            $data,
            [
              'cart_id' => 'required',
              'date' => 'required',
              'restro_id' => 'required',
              'stripe_token' => 'required',
              'token_type' => 'required|in:CARDID,TOKEN',
              'total_amount' => 'required',
              'discount' => 'required',
              'tax_amount' => 'required',
              'paid_amount' => 'required',
            ]
          );
          if ($validator->fails()) {
            $error = $this->validationHandle($validator->messages());
            return response()->json(['status' => false, 'message' => $error]);
          } else {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $unique_id = 'ORD'.mt_rand().$user_id;
            $row = new Order();
            $row->user_id = $user_id;
            $row->order_no = $unique_id;
            $row->restro_id = $request->restro_id;
            $row->date = date('Y-m-d H:i:s',strtotime($request->date));
            $row->total_amount = $request->total_amount;
            $row->discount = $request->discount;
            $row->tax_amount = $request->tax_amount;
            $row->paid_amount = $request->paid_amount;
            $row->stripe_token = $request->stripe_token;
            $row->token_type = $request->token_type;
            $row->status = 'pending';
            if($row->save()){

                $cart_item = CartItem::where('cart_id', $request->cart_id)->get();
                foreach($cart_item as $itm){
                    $ord_item = new OrderItem();
                    $ord_item->order_id = $row->id;
                    $ord_item->dish_id = $itm->dish_id;
                    $ord_item->quantity = $itm->quantity;
                    $ord_item->original_price = $itm->original_price;
                    $ord_item->discount = $itm->discount;
                    $ord_item->best_price = $itm->best_price;
                    $ord_item->save();
                }
            
                  CartItem::where('cart_id', $request->cart_id)->delete();
                  Cart::where('id', $request->cart_id)->update(['cart_amount' => null]);

                  if($row->getRestroDetail->notification_setting == '1'){
                      $message_title = 'Order Placed!';
                      $message = ucfirst($row->getUserDetail->name).' has placed an order #'.$row->order_no.'.';
             
                      Notification::saveNotification($row->restro_id,'restaurant',$row->id,'ORDER_PLACED',$message,$message_title);
             
                    }


                  return response()->json(['status' => true, 'message' => 'Order placed successfully.','data'=>$row]);
                }//end row save 
            }
        } catch (\Exception $e) {
          return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function myOrders(Request $request)
      {
        try {

          $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
          $rows = Order::where('user_id', $user_id)
            ->with(['getRestroDetail','getOrderItems'])
            ->orderBy('orders.id', 'desc')
            ->paginate(10);

          return response()->json(['status' => true, 'message' => 'My Orders','data'=>$rows]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function orderDetail(Request $request)
      {
        try {
          $data = $request->all();
          $validator = Validator::make(
            $data,
            [
              'order_id' => 'required',
            ]
          );
          if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, $this->status_code);
          } else {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $row = Order::where('id', $request->order_id)
                    ->with(['getRestroDetail','getOrderItems'])
                    ->first();

            return response()->json(['status' => true, 'message' => 'Order detail','data'=>$row]);
          }
        } catch (\Exception $e) {
          return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function rateOrder(Request $request)
      {
        try {
          $data = $request->all();
          $validator = Validator::make(
            $data,
            [
              'order_id' => 'required',
              'rating' => 'required',
              'rating_text' => 'required',
            ]
          );
          if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, $this->status_code);
          } else {
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $row = Order::where('id', $request->order_id)->first();
            $row->rating = $request->rating;
            $row->rating_text = $request->rating_text;
            $row->rating_date = date('Y-m-d H:i');
            if($row->save()){
                $restro_id = $row->restro_id;
                // echo $restro_id;die;

                $res = RestaurantDetail::where('user_id',$restro_id)->first();

                $total_rating = Order::select(DB::raw('sum(rating) as total_rating'))
                                    ->where('rating','!=',0)
                                    ->where('restro_id',$restro_id)
                                    ->first();

                 $people_count = Order::select(DB::raw('count(*) as people_count'))
                                    ->where('rating','!=',0)
                                    ->where('restro_id',$restro_id)
                                    ->distinct('user_id')
                                    ->first();  
                                                                      
                $avg_rating = $total_rating->total_rating / $people_count->people_count;  

                $res->total_reviews += 1;
                $res->review_people_count = $people_count->people_count;
                $res->avg_rating = round($avg_rating,0);
                
                $res->save();
            }

            return response()->json(['status' => true, 'message' => 'Order rated successfully.','data'=>$row]);
          }
        } catch (\Exception $e) {
          return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function cancelOrder(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'order_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $row = Order::where('id',$request->order_id)->first();
                if($row){
                    $row->status = "cancelled";
                    if($row->save()){
                         if($row->getRestroDetail->notification_setting == '1'){
                              $message_title = 'Order Cancelled!';
                              $message = $row->getUserDetail->name.' has cancelled order #'.$row->order_no;
                     
                              Notification::saveNotification($row->restro_id,'restaurant',$row->id,'ORDER_CANCELLED',$message,$message_title);
                     
                            }
                        return response()->json(['status' => true, 'message' => 'Order cancelled succssfully.', 'data' => $row]); 
                    }
                     
                }else{
                    return response()->json(['status' => true, 'message' => 'Record not found.', 'data' => []]);  
                }
                            
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getDishDetail(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [

                    'dish_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $row = RestroDish::where('id',$request->dish_id)
                        ->where('is_deleted','!=','1')
                        ->where('is_active','1')
                        ->with(['getcategory','getDishImages'])
                        ->first();
              
                 return response()->json(['status' => true, 'message' => 'Dish Detail', 'data' => $row]);
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

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

    public function restaurantRating(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [

                    'restro_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }else{
                    $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

                    $restro_info = User::where('id',$request->restro_id)
                                    ->select('id','email','name')
                                    ->with(['getOneRestaurantImage','getRestaurantOtherDetail'])
                                    ->first();

                    $rows = Order::where('restro_id',$request->restro_id)
                      ->with('getUserDetail')
                        ->where('rating','!=',0.0)
                        // ->where('user_id','!=',$user_id)
                        ->select(['id','user_id','restro_id','rating_text','rating_date','rating'])
                      /*->select(['id','user_id','restro_id','rating_text','rating_date',
                        DB::raw('round(AVG(orders.rating),1) as ratings_average')
                        ])*/
                      //->groupBy('user_id')
                       ->paginate(10);
                  
                     return response()->json(['status' => true, 'message' => 'Restaurant Rating', 'data' => $rows,'restro_info'=>$restro_info]);
                 }
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

}

