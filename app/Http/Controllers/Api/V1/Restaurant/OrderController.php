<?php

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use App\Models\Order;
use App\Models\RestroDish;
use App\Models\Payment;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Notification;
use App\Models\Setting;
use App\Lib\Stripe;

class OrderController extends Controller
{

    public function restroHome(Request $request)
    {
        try{
            $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;
            $result = array();

            $orders = Order::where('restro_id',$user_id)
                    ->with(['getUserDetail','getOrderItems'])
                    ->where('status','pending')
                    ->orderBy('id','desc')
                    ->limit(5)
                    ->get();
             $total_order_delivered = Order::where('restro_id',$user_id)
                    ->where('status','delivered')
                    ->count();

            $total_dishes = RestroDish::where('restro_id',$user_id)
                            ->where('is_deleted','!=','1')
                            ->count();
                            
            $result = [
                'orders'=> $orders,
                'total_order_delivered'=> $total_order_delivered,
                'total_dishes'=> $total_dishes,
            ];


            return response()->json(['status' => true, 'message' => 'Restaurant Home','data'=>$result]);
                    
        }catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

	public function changeOrderStatus(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'order_id' => 'required',
                    'status' => 'required|in:accept,reject,complete',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::parseToken()->authenticate()->id;
                $row = Order::where('id',$request->order_id)->first();
                if($row){
                    $setting = Setting::select('commission')->first();
                	if($request->status == 'accept'){
                        
                		$row->status = 'confirmed';
                        $row->save();
                		$msg = 'confirmed';


                        //create customer charge
                        try{
                            $stripePay = new Stripe();

                              $description = "Charge for Order: " . $row->id;
                              $extra_params = [
                                'description' => $description,
                                'customer' => $row->getUserDetail->stripe_customer_id
                              ];
                              $strip_res = $stripePay->createCharge($row->token_type, $row->paid_amount, $row->stripe_token, $extra_params);
                              if (isset($strip_res['status']) && $strip_res['status'] == 'succeeded') {

                                $calc_amount = $row->paid_amount - (($row->paid_amount * $setting->commission)/100);
                                $payment = new Payment();
                                $payment->order_id = $row->id;
                                $payment->charge_id = $strip_res['id'];
                                $payment->amount = $calc_amount;
                                $payment->transaction_id = $strip_res['balance_transaction'];
                                $payment->save();

                                // //amount credit in wallet
                                // $wallet = new WalletTransaction();
                                // $wallet->user_id = $row->restro_id;
                                // $wallet->ref_id = $row->id;
                                // $wallet->description = 'Amount Credited';
                                // $wallet->amount = $calc_amount;
                                // $wallet->save();

                                // $user = User::where('id',$row->restro_id)->first();
                                // $user->wallet_amount += $calc_amount;
                                // $user->save();
                              }
                        }catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
                        }
                	}
                    if($request->status == 'reject'){
                		$row->status = 'rejected';
                        $row->save();
                		$msg = 'rejected';
                	}

                    if($request->status == 'complete'){
                        $row->status = 'delivered';
                        $row->save();
                        $msg = 'completed';
                         //amount credit in wallet

                        $calc_amount = $row->paid_amount - (($row->paid_amount * $setting->commission)/100);
                        $wallet = new WalletTransaction();
                        $wallet->user_id = $row->restro_id;
                        $wallet->ref_id = $row->id;
                        $wallet->description = 'Amount Credited';
                        $wallet->amount = $calc_amount;
                        $wallet->save();

                        $user = User::where('id',$row->restro_id)->first();
                        $user->wallet_amount += $calc_amount;
                        $user->save();
                    }
                    
                     if($row->getUserDetail->notification_setting == '1'){
                          $message_title = 'Order '.ucfirst($msg).'!';
                          $message = ucfirst($row->getRestroDetail->name).' has '.$msg.' your order #'.$row->order_no.'.';
                 
                          Notification::saveNotification($row->user_id,'user',$row->id,'ORDER_'.strtoupper($msg),$message,$message_title);
                 
                        }
                	
                	return response()->json(['status' => true, 'message' => 'Order '.$msg.' succssfully.', 'data' => $row]);
                }                
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function myOrders(Request $request){
        try{
            $headers = apache_request_headers();
            $chkToken = chkAuthToken($headers['auth_token']);
            if($chkToken == 1){
                $validator = Validator::make($request->all(), [
                    'status' => 'required|in:pending,confirmed,cancelled,delivered',
                ]);

                if ($validator->fails()) {
                    $error = $this->validationHandle($validator->messages());
                    return response()->json(['status' => false, 'message' => $error]);
                }
                $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

                $rows = Order::where('restro_id',$user_id)
                        ->where('status',$request->status)
                        ->with(['getUserDetail','getOrderItems'])
                        ->orderBy('orders.id', 'desc')
                        ->paginate(10);
                return response()->json(['status' => true, 'message' => 'My Orders','data'=>$rows]);                
            }else{

                return response()->json(['status' => false, 'message' => 'Invailid auth token']);
            }

        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
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
                    ->with(['getUserDetail','getOrderItems'])
                    ->first();

            return response()->json(['status' => true, 'message' => 'Order detail','data'=>$row]);
          }
        } catch (\Exception $e) {
          return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

      public function restaurantRating(Request $request)
      {
        try {
          $user_id = JWTAuth::toUser(JWTAuth::getToken())->id;

          $restro_info = User::where('id',$user_id)
                        ->select('id','email','name')
                        ->with(['getOneRestaurantImage','getRestaurantOtherDetail'])
                        ->first();

            $rows = Order::where('restro_id',$user_id)
                  ->with('getUserDetail')
                    ->where('rating','!=',0)
                  /*->select(['id','user_id','restro_id','rating_text','rating_date',
                    DB::raw('round(AVG(orders.rating),1) as ratings_average')
                    ])*/
                    ->select(['id','user_id','restro_id','rating_text','rating_date','rating'
                    ])
                  //->groupBy('user_id')
                    ->paginate(10);

            return response()->json(['status' => true, 'message' => 'Restaurant Rating','data'=>$rows,'restro_info'=>$restro_info]);
       
        } catch (\Exception $e) {
          return response()->json(['status' => false, 'message' => $e->getMessage().$e->getLine(), 'data' => []]);
        }
      }

    
}