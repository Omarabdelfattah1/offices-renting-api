<?php

// use App\Http\Controllers\API\Auth\EmailVerificationNotificationController;
// use App\Http\Controllers\API\Auth\NewPasswordController;
// use App\Http\Controllers\API\Auth\PasswordResetLinkController;
// use App\Http\Controllers\API\Auth\VerifyEmailController;
use App\Http\Controllers\API\Auth;
// use Illuminate\Support\Facades\Route;

Route::group(['namespace'=>'Auth'],function(){

    Route::post('/register', 'RegisteredUserController@register');

    Route::post('/login', 'AuthController@login')->name('login');

    Route::post('/logout', 'AuthController@logout')
        ->middleware('auth:sanctum')
        ->name('logout');

    Route::post('/forgot-password', 'PasswordResetLinkController@store');

    Route::post('/reset-password', 'NewPasswordController@store')
        ->middleware('guest')
        ->name('password.store');

    Route::get('/verify-email/{id}/{hash}', 'VerifyEmailController@verify')
        ->middleware('auth:sanctum', 'signed', 'throttle:6,1')
        ->name('verification.verify');

    Route::post('/email/verification-notification', 'EmailVerificationNotificationController@send')
        ->middleware('auth:sanctum', 'throttle:6,1')
        ->name('verification.send');

});
