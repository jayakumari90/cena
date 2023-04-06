<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\User\UserRestroController;
use App\Http\Controllers\Api\V1\User\DatingController;
use App\Http\Controllers\Api\V1\User\ChatController;

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
Route::group(['namespace' => 'Api/V1/User'], function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('social-login', [UserController::class, 'socialLogin']);
    Route::post('forgot-password', [UserController::class, 'forgetPassword']);
    Route::post('reset-password', [UserController::class, 'resetPassword']);
    Route::post('check-user', [UserController::class, 'checkUser']);
    Route::post('get-auth-token', [UserController::class, 'getAuthToken']);
    Route::get('version-control', [UserController::class,'versionControl']);   
    Route::post('remove-data', [UserController::class,'removeData']); 
     

    // without authentication
    Route::post('show-home-restaurants', [UserRestroController::class, 'showHomeRestaurant']);
    Route::post('show-all-restaurants', [UserRestroController::class, 'showAllRestaurant']); 
    Route::post('get-restro-detail', [UserRestroController::class, 'restaurantDetail']); 
    Route::post('dish-detail', [UserRestroController::class, 'getDishDetail']);
    Route::get('subscription-plans', [UserController::class, 'getSubscriptionPlans']);

    Route::get('check-notification', [UserController::class, 'checkNotification']);
    Route::get('send-chat-notification', [ChatController::class, 'sendChatNotification']);
    Route::get('cron-payment-success', [UserController::class, 'cronPaymentSuccess']);
    Route::get('cron-expire-free-subscription', [UserController::class, 'cronExpireFreeSubscription']);

});

Route::group(['namespace' => 'Api/V1/User', 'middleware' => ['jwt.verify']], function () {

    //user managment urls
    Route::post('twofactor-code', [UserController::class, 'twoFactorCode']);
    Route::post('send-otp', [UserController::class, 'sendOtp']);
    Route::post('verify-user', [UserController::class, 'verifyUser']);
    Route::get('user-profile', [UserController::class, 'getProfile']);    
    Route::post('edit-profile', [UserController::class, 'editProfile']); 
    Route::post('get-plan', [UserController::class,'getPlan']);
    Route::post('change-password', [UserController::class,'changePassword']); 
    Route::post('user-preference', [UserController::class,'userPreferance']); 
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('upate-badge', [UserController::class, 'updateUserBadge']);
    Route::post('purchase-plan', [UserController::class,'purchasePlan']);
    Route::post('restore-plan', [UserController::class,'restorePlan']);
    Route::get('subscription-status', [UserController::class,'getUserSubscriptionStatus']);

    //dating
    Route::post('dating-preference', [DatingController::class,'datingPreferance']); 
    Route::post('other-user-profile', [DatingController::class, 'otherUserProfile']);
    Route::post('like-dislike-user', [DatingController::class, 'likeDislikeUser']);
    Route::post('get-user-preference', [DatingController::class, 'getUserPreference']);
    Route::post('update-location', [DatingController::class, 'updateLocation']);
    Route::get('my-matching', [DatingController::class, 'matchedUsers']);


    //user restro urls
    Route::get('get-tax-commission', [UserRestroController::class, 'getTaxCommission']);
    Route::post('notification-setting', [UserRestroController::class, 'notificationSetting']);
    Route::post('mark-visit-restro', [UserRestroController::class, 'markVisitRestaurant']);
    Route::post('get-visit-restro', [UserRestroController::class, 'getVisitRestaurant']);
    Route::post('mark-fav-restro', [UserRestroController::class, 'markFavRestaurant']);
    Route::post('get-fav-restro', [UserRestroController::class, 'getFavRestaurant']);
    Route::post('mark-fav-dish', [UserRestroController::class, 'markFavDish']);
    Route::get('get-fav-dish', [UserRestroController::class, 'getFavDish']);
    Route::post('get-restro-with-dish-popularity', [UserRestroController::class, 'restaurantDetailWithDishCategory']);
    Route::post('get-all-dishes-particular-type', [UserRestroController::class, 'getAllDishesWithPopularity']);
    Route::post('menu-items', [UserRestroController::class, 'menuItems']);
    Route::post('menu-dishes', [UserRestroController::class, 'menuDishes']);
    Route::post('restaurant-ratings', [UserRestroController::class, 'restaurantRating']);
    Route::get('notification-listing', [UserRestroController::class, 'notificationListing']);

    //orders api
    Route::post('add-to-cart', [UserRestroController::class, 'addToCart']);
    Route::post('update-cart-item', [UserRestroController::class, 'updateCart']);
    Route::get('get-cart-items', [UserRestroController::class, 'getCartItem']);
    Route::get('get-cart-count', [UserRestroController::class, 'getCartCount']);
    Route::post('checkout', [UserRestroController::class, 'checkout']);
    Route::get('my-orders', [UserRestroController::class, 'myOrders']);
    Route::post('order-detail', [UserRestroController::class, 'orderDetail']);
    Route::post('rate-order', [UserRestroController::class, 'rateOrder']);
    Route::post('cancel-order', [UserRestroController::class, 'cancelOrder']);


    //chats api
    Route::post('block-user', [ChatController::class, 'blockUser']);
    Route::post('report-user', [ChatController::class, 'reportUser']);
    Route::post('upload-chat-image', [ChatController::class, 'uploadChatImage']);


    
    

});

