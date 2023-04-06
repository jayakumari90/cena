<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Restaurant\RestaurantController;
use App\Http\Controllers\Api\V1\Restaurant\DishController;
use App\Http\Controllers\Api\V1\Restaurant\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::group(['namespace' => 'Api/V1/Restaurant'], function () {
    Route::get('restaurant-cat', [RestaurantController::class, 'restaurantCat']);
    Route::get('dish-cat', [RestaurantController::class, 'dishCat']);
    Route::post('check-restaurant', [RestaurantController::class, 'checkRestaurant']);
    Route::post('register', [RestaurantController::class, 'register']);
    
    Route::post('social-login', [RestaurantController::class, 'socialLogin']);
    Route::post('onboard-restro', [RestaurantController::class, 'onboardRestaurant']);
    Route::post('login', [RestaurantController::class, 'login']);
    Route::post('forgot-password', [RestaurantController::class, 'forgetPassword']);
    Route::post('reset-password', [RestaurantController::class, 'resetPassword']);
    Route::post('send-otp', [RestaurantController::class, 'sendOtp']);

    Route::get('subscription-plans', [RestaurantController::class, 'getSubscriptionPlans']);
});

Route::group(['namespace' => 'Api/V1/Restaurant', 'middleware' => ['jwt.verify']], function () {
    Route::post('twofactor-code', [RestaurantController::class, 'twoFactorCode']);
    Route::post('verify-user', [RestaurantController::class, 'verifyUser']);
    Route::post('restaurant-profile', [RestaurantController::class, 'getProfile']);
    Route::post('edit-profile', [RestaurantController::class, 'editProfile']);
    Route::post('change-password', [RestaurantController::class, 'changePassword']);
    Route::post('logout', [RestaurantController::class, 'logout']);
    Route::post('notification-setting', [RestaurantController::class, 'notificationSetting']);
    Route::post('upate-badge', [RestaurantController::class, 'updateUserBadge']);
    

    //stripe
    Route::get('stripe-connection', [RestaurantController::class, 'stripeConnection']);
    Route::post('wallet-to-bank-transfer', [RestaurantController::class, 'walletToBankTransfer']);
    Route::get('get-wallet-history', [RestaurantController::class, 'getWalletHistory']);
    Route::get('my-wallet', [RestaurantController::class, 'myWallet']);
    Route::get('notification-listing', [RestaurantController::class, 'notificationListing']);

    //dishes
    Route::post('add-dish', [DishController::class, 'addDish']);
    Route::post('edit-dish', [DishController::class, 'editDish']);
    Route::post('delete-dish', [DishController::class, 'deleteDish']);
    Route::post('get-dishes', [DishController::class, 'getDishes']);
    Route::post('dish-detail', [DishController::class, 'getDishDetail']);
    Route::post('active-deactive-dish', [DishController::class, 'activeDeactiveDish']);


    //orders
    Route::get('restaurant-home', [OrderController::class, 'restroHome']);
    Route::post('change-order-status', [OrderController::class, 'changeOrderStatus']);
    Route::post('my-orders', [OrderController::class, 'myOrders']);
    Route::post('order-detail', [OrderController::class, 'orderDetail']);
    Route::get('restaurant-rating', [OrderController::class, 'restaurantRating']);
});