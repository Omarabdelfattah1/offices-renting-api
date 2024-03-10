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
    Route::apiResource("offices",'OfficesController')->except(['index','show']);
    // Route::get("{resource_type}/{resource_id}/images",'ImagesController@index');
    Route::apiResource("{resource_type}/{resource_id}/images",'ImagesController')->except('show','update');
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
