<?php

use Illuminate\Support\Facades\Route;
// use Laravel\Socialite\Facades\Socialite;

// Route::get('/auth/{driver}/redirect', function ($driver) {
//     if(!in_array($driver,config('services.social_dirvers'))){
//         abort(404);
//     }
//     return Socialite::driver($driver)->redirect();
// });
// Route::get('/auth/google/callback', function () {
//     $user = Socialite::driver('google')->user();
//     dd( $user);
// });
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
    return ['Laravel' => app()->version()];
});

