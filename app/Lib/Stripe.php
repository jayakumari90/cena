<?php
namespace App\Lib;
use Config;

/**
 * 
 */
class Stripe
{
	public $secret_key = '';
	function __construct()
	{
		 $this->secret_key = Config::get('constants.STRIPE_SECRET','');
         \Stripe\Stripe::setApiKey($this->secret_key);
	}

	 public static function getCurrency(){
        return 'CAD';
    }

	public static function createCustomer($email){
		try {
			
			\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
			$customer = \Stripe\Customer::create([ 
		   		"email" => $email
			]);
			$response['status'] = true;
			$response['customer_id'] = $customer->id;
			$response['message'] = "Stripe User Created.";
		} catch (Exception $e) {
			$response['status'] = false;
			$response['customer_id'] = null;
			$response['message'] = $e->getMessage();
		}
		return $response;
	}

	public static function createCharge($token_type,$amount, $card_key, $extra_params=[]) {

			// echo self::getCurrency();die;
		if($token_type == "CARDID"){
			$charges = \Stripe\Charge::create([
	            "amount" => $amount * 100,
	            "currency" => self::getCurrency(),
	            "source" => $card_key, 
	            "description" => isset($extra_params['description']) ? $extra_params['description'] : '',
	            "customer" => $extra_params['customer']
	        ]);
		}else{
			$charges = \Stripe\Charge::create([
	            "amount" => $amount * 100,
	            "currency" => self::getCurrency(),
	            "source" => $card_key 
	            // "description" => isset($extra_params['description']) ? $extra_params['description'] : '',
	            // "customer" => $extra_params['customer']
	        ]);
		}
		

		// print_r($charges);die;   
        return $charges;
    }

	public static function createCard($customer_id,$cardDetails){
		try {
			\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
			$token = \Stripe\Token::create([
			  'card' => [
			    'number'	=>		$cardDetails['card_number'],
			    'exp_month'	=>		$cardDetails['exp_month'],
			    'exp_year'	=>		$cardDetails['exp_year'],
			    'name'		=>		$cardDetails['name']
			  ]
			]);
			if($token){
				$card = \Stripe\Customer::createSource(
				  $customer_id,
				  [
				    'source' => $token->id,
				  ] 
				);
				$response['status'] = true; 
				$response['message'] = "Card Added.";
				$response['data'] = $card;
			}else{
				$response['status'] = false; 
				$response['message'] = "Token Error.";
			}
		} catch (Exception $e) {
			$response['status'] = false; 
			$response['message'] = $e->getMessage();
		}
		return $response;
	}

	 public static function transfer($amount,$account){
    	try {
	    	\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
	    	$transfer = \Stripe\Transfer::create(array(
	    		'amount'		=>		$amount*100,
	    		'currency'		=>		self::getCurrency(),
	    		'destination'	=>		$account
	    	));
	    	$response['status'] = true;
            $response['data'] = $transfer;
            $response['message'] = "Transfered successfully.";
        }catch (\Exception $e) {
    		$response['status'] = false;
            $response['charge'] = [];
            $response['message'] = $e->getMessage();	
    	}
    	return $response;
    }

	public static function getCardList($customer_id){
		try {
			\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
			$cards = \Stripe\Customer::allSources(
			  $customer_id,
			  [ 
			    'object' => 'card'
			  ]
			);  
			if($cards->data){ 
				$response['data'] = $cards->data;
				$response['status'] = true; 
				$response['message'] = "Cards List.";
			}else{
				$response['data'] = [];
				$response['status'] = false; 
				$response['message'] = "No Card Found.";
			}
		} catch (Exception $e) {
			$response['status'] = false; 
			$response['message'] = $e->getMessage();
		}
		return $response;
	}

	public static function deleteCard($customer_id,$card_id){
		try {
			\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
			$delete = \Stripe\Customer::deleteSource(
			  $customer_id,
			  $card_id
			); 
			if($delete->deleted===true){ 
				$response['status'] = true; 
				$response['message'] = "Cards has been deleted successfully.";
			}else{
				$response['status'] = false; 
				$response['message'] = "Error accured.";
			}
		} catch (Exception $e) {
			$response['status'] = false; 
			$response['message'] = $e->getMessage();
		}
		return $response;
	}

	public static function setDefaultCard($customer_id,$card_id){
		try {
			\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
			$cu = \Stripe\Customer::retrieve($customer_id);
			$cu->default_source = $card_id; 
			if($cu->save()){ 
				$response['status'] = true; 
				$response['message'] = "Default source updated successfully.";
			}else{
				$response['status'] = false; 
				$response['message'] = "Error accured.";
			}
		} catch (Exception $e) {
			$response['status'] = false; 
			$response['message'] = $e->getMessage();
		}
		return $response;
	}

	public static function createToken($customer_key, $card_key,$connect_account){
		\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
		//echo $customer_key."<br>";
		//echo $card_key."<br>";
        $token = \Stripe\Token::create(array(
            "customer" => $customer_key,
            'card'=>$card_key
        ),array("stripe_account" => $connect_account));
        //dd($token);
        return $token;   
    }

    /*
        * This function user for hold payment for 7 days 
        * @params request $amount, $customer_key, $card_key, $currency, $connect_account, $application_fee
        * @return string

    */
    public static function makeHoldPayment($amount, $token,$customer_id) {
        try {
        	\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
            $charge = \Stripe\Charge::create(array(
	                "amount" => $amount*100,
	                "currency" => 'usd',
	                //"source" => 'tok_visa', //$token 
	                "source" => $token, //$token 
	                'capture' => true,
	                //"customer"=>$customer_id
	            )
            );
            
            $response['status'] = true;
            $response['data'] = $charge;
            $response['message'] = "Charge created successfully.";
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['data'] = [];
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public static function makeHoldPaymentUser($amount, $token,$customer_id,$stripe_user_id) {
        try {
         	\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
            $charge = \Stripe\Charge::create(array(
	                "amount" =>round($amount,2)*100,
	                "currency" => 'usd',
	                //"source" => 'tok_visa', //$token 
	                "source" => $token, //$token 
	                'capture' => true,
	                //"customer"=>$customer_id,
	                "transfer_data"=>array("destination"=>$stripe_user_id)
	            )
            );
            
            $response['status'] = true;
            $response['data'] = $charge;
            $response['message'] = "Charge created successfully.";
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['data'] = [];
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

     /*
        * This function user for release hold payment by default after seven day automatically charged 
        * @params request $charge_id, $stripe_account
        * @return array

    */
    public static function caputureHoldedCharge($charge_id=null, $stripe_account){
    	try {
    		\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
            $charge = \Stripe\Charge::retrieve($charge_id, ["stripe_account" => $stripe_account]);
            $charge = $charge->capture();
            $response['status'] = true;
            $response['charge'] = $charge;
            $response['message'] = "Charge captured successfully.";
    	} catch (\Exception $e) {
    		$response['status'] = false;
            $response['charge'] = [];
            $response['message'] = $e->getMessage();	
    	}
    	return $response;
    }
    
    /*
        * This function user for refund hold payment  
        * @params request $charge_id, $stripe_account
        * @return array

    */ 
    public static function refundCharge($charge_id=null, $amount=0){
        try {
        	\Stripe\Stripe::setApiKey(Config::get('constants.STRIPE_SECRET'));
        	$refund = \Stripe\Refund::create([
		      	'charge' => $charge_id,
		    	'amount' => $amount,
			]);
			$response['status'] = true;
            $response['refund'] = $refund;
            $response['message'] = "Charge refunded.";
        } catch (\Exception $e) {
        	$response['status'] = false;
            $response['refund'] = [];
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}