<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

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
Route::get("/tags", 'TagsController');
Route::get("offices",'OfficesController@index');
Route::get("offices/{office}",'OfficesController@show');
Route::get('/auth/{driver}/redirect', 'Auth\SocialAuthController@redirect');
Route::get('/auth/{driver}/callback','Auth\SocialAuthController@callback');

Route::middleware('auth:sanctum')->group(function () {
    // Officespl,
    Route::apiResource("offices",'OfficesController')->except(['index','show']);
    // Images
    Route::apiResource("{resource_type}/{resource_id}/images",'ImagesController')->except('show','update');
    // Reserations
    Route::get('reservations','ReservationsController@index');
    Route::post('reservations','ReservationsController@store');
    Route::get('my-reservations','ReservationsController@my_reservations');
    Route::get('office-reservations','ReservationsController@office_reservations');


    Route::get('/user',function(Request $request){
        return $request->user();
    });
});

require __DIR__.'/auth.php';
