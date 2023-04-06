<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AppPageController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Admin\StrugglingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReportController;
use App\Models\User;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('admin.home');
});
Route::fallback(function () {

    return view("errors/404");

});
//------------------------------------------Clear cache and config------------------------------------------
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
});

  Auth::routes([
        'register' => false
    ]);  

Route::get('deletion/{id}', [AdminController::class,'checkDeletionStatus']);
Route::group(['namespace' => 'Admin', 'middleware' => ['auth']], function () {
Route::get('/home', [AdminController::class, 'adminHome'])->name('admin.home')->middleware('is_admin');
Route::get('/user', [AdminController::class, 'showUser'])->name('admin.user')->middleware('is_admin');
Route::get('/update-status', [AdminController::class,'activateUser'])->name('admin.activate');
Route::get('/edit', [AdminController::class, 'editProfile'])->name('admin.editprofile')->middleware('is_admin');
Route::post('/edit', [AdminController::class, 'editProfile'])->name('admin.editprofile')->middleware('is_admin');
Route::get('/changepassword', [AdminController::class, 'changePassword'])->name('admin.changepassword')->middleware('is_admin');
Route::post('/changepassword', [AdminController::class, 'changePassword'])->name('admin.changepassword')->middleware('is_admin');
Route::get('/profile/{id}', [AdminController::class, 'viewUserProfile'])->name('admin.userprofile')->middleware('is_admin');

//---------------------------------AppPages----------------------------------
    Route::get('/appPage', [AppPageController::class, 'appPageList'])->name('appPage');
    Route::get('/appPage/list', [AppPageController::class, 'showAppPageList'])->name('appPage.list');
    Route::post('/appPage/detail', [AppPageController::class, 'viewAppPageDetail'])->name('appPage.detail');
    Route::post('/appPage/edit', [AppPageController::class, 'editAppPage'])->name('appPage.edit');

//---------------------------------Subscriptions----------------------------------
    Route::get('/plan', [PlanController::class, 'index'])->name('plan');
    Route::get('/plan/list', [PlanController::class, 'planListing'])->name('plan.list');
    Route::post('/plan/detail', [PlanController::class, 'viewPlan'])->name('plan.detail');
    Route::post('/plan/edit', [PlanController::class, 'editPlan'])->name('plan.edit');
//---------------------------------Settings----------------------------------

    Route::any('/settings', [SettingsController::class,'setting'])->name('settings');


    //-----------------------------reported user listing----------------------------------

    Route::get('/reported-users', [ReportController::class,'index'])->name('reported.users.index');
    Route::post('/reported-users/list', [ReportController::class,'showReportedUserList'])->name('reported.users.list');
    Route::post('report-detail',[ReportController::class,'reportDetail'])->name('reported.users.detail');
    
    Route::any('/reported-users/deactivate/{id}', [ReportController::class,'deactivateReportedUser'])->name('reported.users.deactivate');


//---------------------------------Restaurant---------------------------------
    Route::get('/restaurant-cat', [RestaurantController::class,'restauraCatList'])->name('restaurant_cat');
    Route::get('/restaurant-cat/list', [RestaurantController::class,'showRestauraCatList'])->name('restaurant_cat.list');
    Route::get('/restaurant-cat/add', [RestaurantController::class,'addRestauraCat'])->name('restaurant_cat.add');
    Route::post('/restaurant-cat/add', [RestaurantController::class,'addRestauraCat'])->name('restaurant_cat.add');
    Route::post('/restaurant-cat/detail', [RestaurantController::class, 'restroCatDetail'])->name('restaurant_cat.detail');
    Route::post('/restaurant-cat/edit', [RestaurantController::class, 'editRestaurantCat'])->name('restaurant_cat.edit');
    Route::get('/restaurant-cat/delete/{id}', [RestaurantController::class,'deleteCat'])->name('restaurant_cat.delete');

    Route::get('/restaurant',[RestaurantController::class,'restaurantList'])->name('admin.restaurant');
    Route::get('/restaurant/list',[RestaurantController::class,'showRestaurantList'])->name('restaurant.list');
    Route::get('/restaurant/activate',[RestaurantController::class,'activateUser'])->name('restaurant.activate');
    Route::get('/restaurant/deactivate/{id}',[RestaurantController::class,'deactivateUser'])->name('restaurant.deactivate');

    Route::get('/restaurant/approve/{id}',[RestaurantController::class,'approveUser'])->name('restaurant.approve');
    Route::get('/restaurant/disapprove/{id}',[RestaurantController::class,'disapproveUser'])->name('restaurant.disapprove');

    Route::get('/restaurant/restro-detail/{id}',[RestaurantController::class,'viewRestroProfile'])->name('admin.restroprofile');
    Route::get('/restaurant/struglling',[RestaurantController::class,'changeStruglling'])->name('restaurant.struglling');

//-------------------------------------Dish-------------------------------------------
    Route::get('/dish-cat', [DishController::class,'dishCatList'])->name('dish_cat');
    Route::get('/dish-cat/list', [DishController::class,'showDishCatList'])->name('dish_cat.list');
    Route::post('/dish-cat/add', [DishController::class,'addDishCat'])->name('dish_cat.add');
    Route::get('/dish-cat/add', [DishController::class,'addDishCat'])->name('dish_cat.add');
    Route::post('/dish-cat/detail', [DishController::class, 'dishCatDetail'])->name('dish_cat.detail');
    Route::post('/dish-cat/edit', [DishController::class, 'editDishCat'])->name('dish_cat.edit');
    Route::get('/dish-cat/delete/{id}', [DishController::class,'deleteCat'])->name('dish_cat.delete');

    Route::get('/dish',[DishController::class,'dishList'])->name('admin.dish');
    Route::get('/dish/list',[DishController::class,'showDishList'])->name('dish.list');;
    Route::get('/dish/dish-detail/{id}',[DishController::class,'viewDishDetail'])->name('admin.dishdetail');

    //---------------------------------Orders----------------------------------
    Route::any('/order', [OrderController::class, 'OrderList'])->name('order');
    Route::any('/order/list', [OrderController::class, 'showOrderList'])->name('order.list');
    Route::get('/order/view/{id}', [OrderController::class, 'viewOrder'])->name('order.view');

    Route::get('admin/logout', [AdminController::class, 'logout'])->name('admin.logout')->middleware('is_admin');
});

//---------------------------------Struggling---------------------------------
    Route::get('/struggling', [StrugglingController::class,'struglingList'])->name('struggling');
    Route::get('/struggling/list', [StrugglingController::class,'showStruglingList'])->name('struggling.list');
    Route::post('/struggling/add', [StrugglingController::class,'addStrugling'])->name('struggling.add');
    Route::get('/struggling/add', [StrugglingController::class,'addStrugling'])->name('struggling.add');
    Route::get('/struggling/delete/{id}', [StrugglingController::class,'deletestuggling'])->name('struggling.delete');
    Route::post('/struggling/detail', [StrugglingController::class, 'strugglingDetail'])->name('struggling.detail');
    Route::post('/struggling/edit', [StrugglingController::class, 'editStruggling'])->name('struggling.edit');


Route::group(['namespace' => 'Front'], function () {
    Route::get('page/{slug}', [FrontController::class, 'showAppPage'])->name('show.page');
    Route::get('activate-account/{token}', [FrontController::class, 'activateAccount']);
    Route::get('reset-password/{token}', [FrontController::class, 'resetPassword'])->name('user.resetPassword');
    Route::post('reset-password/{token}', [FrontController::class, 'setNewPassword'])->name('user.resetPassword');

    //stripe connect
    Route::get('/create-connect', 'StripeConnectController@createConnect')->name('create-connect');
    Route::get('/create-connect-success', 'StripeConnectController@createConnectSuccess')->name('create-connect-success');
});