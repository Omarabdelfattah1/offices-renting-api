<?php
namespace App\Services;

use App\Models\User;
use Exception;

class TelegramService {
    protected $token = '';
    public function __construct(){
        $this->token = config('services.telegram.token');
    }
    function checkTelegramAuthorization($auth_data) {
      $check_hash = $auth_data['hash'];
      unset($auth_data['hash']);
      $data_check_arr = [];
      foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
      }
      sort($data_check_arr);
      $data_check_string = implode("\n", $data_check_arr);
      $secret_key = hash('sha256', $this->token, true);
      $hash = hash_hmac('sha256', $data_check_string, $secret_key);
      if (strcmp($hash, $check_hash) !== 0) {
        throw new Exception('Data is NOT from Telegram');
      }
      if ((time() - $auth_data['auth_date']) > 86400) {
        throw new Exception('Data is outdated');
      }
      return $auth_data;
    }

    function saveTelegramUserData($auth_data) {
      return User::create([
        'first_name' => $auth_data['first_name'],
        'telegram_id'=> $auth_data['id']
      ]);
    }
    public function redirect(){
        $botToken = config('services.telegram.token');
        $redirectUrl = config('services.telegram.redirect'); // URL where users will be redirected after authentication
        $telegramAuthUrl = "https://api.telegram.org/bot$botToken/login?redirect_url=$redirectUrl";
        return redirect($telegramAuthUrl);
    }

}
