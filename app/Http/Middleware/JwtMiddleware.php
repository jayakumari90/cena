<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        try{
            $token = JWTAuth::getToken();
            $user = JWTAuth::toUser($token);
            if(empty($user)){
                return response()->json(['status'=>false,'message'=>'Your account is deleted by admin'],201);
            }elseif(empty($user->status)){
                return response()->json(['status'=>false,'message'=>'Your account is deactivated by admin'],201);
            }
        }catch (JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status'=>false,'message'=>'token_expired']);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status'=>false,'message'=>'token_invalid']);
            }else{
                return response()->json(['status'=>false,'message'=>'Token is required']);
            }
        }
        
       return $next($request);
    }
    
    // public function handle($request, Closure $next)
    // {
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //     } catch (Exception $e) {
    //         if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
    //             return response()->json(['status' => 'Token is Invalid']);
    //         }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
    //             return response()->json(['status' => 'Token is Expired']);
    //         }else{
    //             return response()->json(['status' => 'Authorization Token not found']);
    //         }
    //     }
    //     return $next($request);
    // }

}