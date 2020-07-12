<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Restaurant
Route::group(['prefix'=>'v1','namespace'=>'Api\Resturant'],function(){
    Route::post('registerResturant','AuthResturantController@registerResturant');
    Route::post('loginResturant','AuthResturantController@loginResturant');
    Route::post('restPasswordResturant','AuthResturantController@restPasswordResturant');
    Route::post('newPassword','AuthResturantController@newPasswordResturant');

    Route::group(['middleware' => 'auth:resturant_api'], function () {
        Route::post('commentResturant','AuthResturantController@commentResturant');
        Route::post('profileResturant','AuthResturantController@profileResturant');
        Route::post('contactResturant','AuthResturantController@contactResturant');
        Route::post('registerToken','AuthResturantController@registerTokenResturant');
        Route::post('removeToken','AuthResturantController@removeTokenResturant');

        Route::post('newOrder','MainResturantController@newOrder');
        Route::post('currentOrder','MainResturantController@currentOrder');
        Route::post('lastOrder','MainResturantController@lastOrder');
        Route::post('acceptOrder','MainResturantController@acceptOrder');
        Route::post('rejectOrder','MainResturantController@rejectOrder');
        Route::post('notificationList','MainResturantController@notificationList');
        Route::post('notificationUpdate','MainResturantController@notificationUpdate');

    });
});
Route::group(['prefix'=>'v1','namespace'=>'Api\Resturant'],function(){

    Route::post('addCategory','MainResturantController@addCategory');
    Route::post('editCategory','MainResturantController@editCategory');
    Route::post('deleteCategory','MainResturantController@deleteCategory');

    Route::post('addProduct','MainResturantController@addProduct');
    Route::post('editProduct','MainResturantController@editProduct');
    Route::post('deleteProduct','MainResturantController@deleteProduct');

    Route::post('addOffers','MainResturantController@addOffers');
    Route::post('editOffers','MainResturantController@editOffers');
    Route::post('deleteOffers','MainResturantController@deleteOffers');


});





////client
Route::group(['prefix'=>'v1','namespace'=>'Api\Client'],function(){
    Route::post('registerClient','AuthClientController@registerClient');
    Route::post('loginClient','AuthClientController@loginClient');
    Route::post('restPasswordClient','AuthClientController@restPasswordClient');
    Route::post('newPasswordClient','AuthClientController@newPasswordClient');

    Route::group(['middleware' => 'auth:client_api'], function () {
        Route::post('profileClient','AuthClientController@profileClient');
        Route::post('offers','AuthClientController@offers');
        Route::post('contactClient','AuthClientController@contactClient');
        Route::post('registerToken','AuthClientController@registerTokenResturant');
        Route::post('removeToken','AuthClientController@removeTokenResturant');

        Route::post('addComment','MainClientController@addComment');

        Route::post('clientOrders','MainClientController@clientOrders');
        Route::post('orderDetails','MainClientController@orderDetails');
        Route::post('newOrder','MainClientController@newOrder');
        Route::post('currentOrder','MainClientController@currentOrder');
        Route::post('lastOrder','MainClientController@lastOrder');
        Route::post('deliverOrder','MainClientController@deliverOrder');
        Route::post('declineOrder','MainClientController@declineOrder');
        Route::post('notificationList','MainClientController@notificationList');
        Route::post('notificationUpdate','MainClientController@notificationUpdate');
    });
});


Route::group(['prefix'=>'v1','namespace'=>'Api\Client'],function(){
    Route::post('restaurants','MainClientController@restaurants');
    Route::post('categories','MainClientController@categories');
    Route::post('productsBycategory','MainClientController@productsBycategory');

});




////General
Route::group(['prefix'=>'v1','namespace'=>'Api'],function(){
    Route::post('restaurants','GeneralController@restaurants');
    Route::post('cities','GeneralController@cities');
    Route::post('districts','GeneralController@districts');
    Route::post('settings','GeneralController@settings');
    Route::post('categories','MainController@categories');

});