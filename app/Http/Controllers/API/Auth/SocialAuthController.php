<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect($driver) {
        if(!in_array($driver,config('services.social_dirvers'))){
            abort(404);
        }
        return Socialite::driver($driver)->stateless()->redirect();
    }
    public function callback($driver)
    {
        if(!in_array($driver,config('services.social_dirvers'))){
            abort(404);
        }
        request()->validate([
            "code" => "required",
            "scope" => "required",
            "authuser" => "required",
            "prompt" => "required",
        ]);
        try{
            $userDetails = Socialite::driver($driver)->stateless()->user();
            $user = User::where('email',$userDetails->getEmail())->first();
            if(is_null($user)){
                $user = User::create([
                    'first_name' => $userDetails->getName(),
                    'last_name' => $userDetails->getName(),
                    'email' => $userDetails->getEmail()
                ]);
            }
            return $this->success([
                "token" => $user->createToken('new-token')->plainTextToken
            ]);
        }catch(Exception $exception){
            return $this->error(trans('response.error'),500,$exception);
        }
    }
}
