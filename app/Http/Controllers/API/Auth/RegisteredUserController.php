<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(RegisterRequest $request)
    {
        try{
            DB::beginTransaction();
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);
            event(new Registered($user));
            DB::commit();
            return $this->successWithData([
                'token' => $user->createToken('new-token')->plainTextToken
            ],201);
        }catch(Exception $exception){
            DB::rollBack();
            return $this->error(trans('response.error'),500,$exception);
        }
    }
}
