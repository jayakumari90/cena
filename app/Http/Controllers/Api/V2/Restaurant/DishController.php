<?php

namespace App\Http\Controllers\Api\V2\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use App\Models\RestroDish;
use App\Models\DishImage;
use App\Models\CartItem;

class DishController extends Controller
{
    
    public function addDish(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'dish_category' => 'required',
                    'dish_name' => 'required',
                    'price' => 'required',
                    'description' => 'required',
                    // 'discount' => 'required',
                    'is_popular' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $dish = new RestroDish;
                $dish->restro_id = $user_id;
                $dish->category_id = $request->dish_category;
                $dish->name = $request->dish_name;
                $dish->price = $request->price;
                $dish->description =$request->description;
                $dish->is_popular =$request->is_popular;
                $dish->discount = isset($request->discount) ? $request->discount:0;
                if($dish->save()){

                      if(!empty($request->dish_images)){
                            DishImage::where('dish_id',$dish->id)->delete();
                            $files =$request->dish_images;
                            foreach($files as $images){

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
                     $row = RestroDish::where('id',$dish->id)
                        ->where('is_deleted','!=','1')
                        ->with(['getcategory','getDishImages'])
                        ->first();
                    return response()->json(['status' => true, 'message' => 'Dish added succssfully.', 'data' => $row]);
                }
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function editDish(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [

                    'dish_id' => 'required',
                    'dish_category' => 'required',
                    'dish_name' => 'required',
                    'price' => 'required',
                    'description' => 'required',
                    // 'discount' => 'required',
                    'is_popular' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $dish = RestroDish::where('id',$request->dish_id)->first();
                $dish->restro_id = $user_id;
                $dish->category_id = $request->dish_category;
                $dish->name = $request->dish_name;
                $dish->price = $request->price;
                $dish->description =$request->description;
                $dish->is_popular =$request->is_popular;
                $dish->discount = $request->discount;
                if($dish->save()){

                      if(!empty($request->dish_images)){
                            DishImage::where('dish_id',$dish->id)->delete();
                            $files =$request->dish_images;
                            foreach($files as $images){

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
                        $row = RestroDish::where('id',$dish->id)
                        ->where('is_deleted','!=','1')
                        ->with(['getcategory','getDishImages'])
                        ->first();
                    return response()->json(['status' => true, 'message' => 'Dish added succssfully.', 'data' => $row]);
                }
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function deleteDish(Request $request){
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
                $dish = RestroDish::where('id',$request->dish_id)->update(['is_deleted'=>'1']);
              
                 return response()->json(['status' => true, 'message' => 'Dish deleted succssfully.', 'data' => []]);
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function activeDeactiveDish(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [

                    'is_active' => 'required|in:0,1',
                    'dish_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $dish = RestroDish::where('id',$request->dish_id)->update(['is_active'=>(string)$request->is_active]);

                //deleting cart dish when dish deactivating dish
                if((string)$request->is_active != '1'){
                    $cart = CartItem::where('dish_id',$request->dish_id)->delete();
                }
                
                $msg = (string)$request->is_active == '1'?'activated':'deactivated';
                 return response()->json(['status' => true, 'message' => 'Dish '.$msg.' succssfully.', 'data' => []]);
                
            }else{
                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function getDishes(Request $request){
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
                }
                $rows = RestroDish::where('restro_id',$request->restro_id)
                        ->where('is_deleted','!=','1')
                        ->with(['getcategory','getDishImages'])
                        ->get();
              
                 return response()->json(['status' => true, 'message' => 'Dishes', 'data' => $rows]);
                
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


}

