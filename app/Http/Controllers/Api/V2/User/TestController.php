<?php

namespace App\Http\Controllers\Api\V2\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TomorrowIdeas\Plaid\Plaid;
use TomorrowIdeas\Plaid\Entities\User;
use DateTime;
class TestController extends Controller
{
    public function create() {
        $clientId  = "617946fb6aaa7c0011b0a7a5";
        $secretKey = "f4f29986df9b3922db94640d1aaaa5";
        $env = "sandbox";
        // $plaid = new Plaid(
        //     $clientId,
        //     $secretKey,
        //     $env
        // );
      try {
        $access_token = "link-sandbox-64847c59-2b87-4180-976f-fa7ea1dd2648";
         $postRequest = array(
          "client_id"=> $clientId,
          "secret"=> $secretKey,
          "public_token"=> $access_token        
          //"link_token"=> $access_token        
           
        );

        
        $cURLConnection = curl_init('https://sandbox.plaid.com/link/token/get');
         //$cURLConnection = curl_init('https://sandbox.plaid.com/transactions/get');
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, json_encode($postRequest));
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
                                                    'Content-Type: application/json',
                                                    'Connection: Keep-Alive'
                                                    ));
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        $apiResponse = curl_exec($cURLConnection);
        curl_close($cURLConnection);

        // $apiResponse - available data from the API request
        $jsonArrayResponse = json_decode($apiResponse);

        dd($jsonArrayResponse);
      } catch ( PlaidRequestException $exception) {
         
         return dd($exception->getResponse());
      }
        
    }

   

  
}

