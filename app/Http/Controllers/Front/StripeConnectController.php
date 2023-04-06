<?php 
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use Session;
// use Stripe;
use App\Models\User;

use App\Lib\Stripe;
class StripeConnectController extends Controller
{
    
    public function createConnect(Request $request){
// dd("aa");
        try {
           
            $code = $request->get('code');
            $state = $request->get('state'); 
            if($code || $state){
                $post = [
                    'client_secret' => env('STRIPE_SECRET'),
                    'code' => $code,
                    'grant_type'   => 'authorization_code',
                ];

                $ch = curl_init('https://connect.stripe.com/oauth/token');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
                // execute!
                $response = curl_exec($ch); 
               
                // close the connection, release resources used
                curl_close($ch); 
                // do anything you want with your response
                $res = json_decode($response,true);  

                if($res){
                   
                    $user = User::where('stripe_customer_id',$state)->first();

                    if($user){ 

                        $user->stripe_user_id = $res['stripe_user_id'];
                        $user->save();
                        //code for stripe
                        // \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                        $stripe = new \Stripe\StripeClient(
                          env('STRIPE_SECRET')
                        );
                        $stripe->accounts->updateCapability(
                          $res['stripe_user_id'],
                          'transfers',
                          ['requested' => true]
                        );


                       return redirect('create-connect-success');
                    }else{
                        return abort(500);
                    }
                }else{
                    return abort(500);
                }
            }else{
                return abort(500);
            }
        } catch (\Exception $e) {
            return abort(500);
        }
    }

    public function createConnectSuccess(){
        $message = "You are all set with your stripe account, please wait while we redirect you to the app. <br> Redirecting...";
        return view('front.stripe-connect',compact('message'));
    }

}
