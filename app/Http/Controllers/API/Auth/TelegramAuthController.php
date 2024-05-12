<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Exception;

class TelegramAuthController extends Controller
{
    public function redirect() {

        $bot_username = config('services.telegram.bot_name');
        $redirect = config('services.telegram.redirect');
        return view('telegram-login',compact('bot_username','redirect'));
    }
    public function callback()
    {
        $telegram = new TelegramService();
        try {
            $auth_data = $telegram->checkTelegramAuthorization($_GET);
            $user = $telegram->saveTelegramUserData($auth_data);
            return $this->success([
                "token" => $user->createToken('new-token')->plainTextToken
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
