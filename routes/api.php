<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get("/tags", 'TagsController');
Route::get("offices",'OfficesController@index');
Route::get("offices/{office}",'OfficesController@show');
Route::middleware('auth:sanctum')->group(function () {
    // Offices
    Route::apiResource("offices",'OfficesController')->except(['index','show']);
    // Images
    Route::apiResource("{resource_type}/{resource_id}/images",'ImagesController')->except('show','update');
    // Reserations
    Route::get('reservations','ReservationsController@index');
    Route::post('reservations','ReservationsController@store');
    Route::get('my-reservations','ReservationsController@my_reservations');
    Route::get('office-reservations','ReservationsController@office_reservations');
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
