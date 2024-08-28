<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function getToken()
    {
        $res = Http::post('notify.eskiz.uz/api/auth/login', [
            'email' => 'volmir.kim01@gmail.com',
            'password' => 'lxIk91uC6ESSoOgtmzmFNkqhqZa4dCuBYu259ClY'
        ]);

        if ($res->ok()) {
            if (isset($res['data']['token'])) {
                return $res['data']['token'];
            } else {
                abort(400, 'Token not found in response');
            }
        } else {
            Log::error('Authentication failed', ['response' => $res->body()]);
            abort(400, 'Authentication failed');
        }
    }

    public function sendMessage($phoneNumber, $message): string
    {
        return Http::withToken($this->getToken())->post('notify.eskiz.uz/api/message/sms/send', [
            'mobile_phone' => $phoneNumber,
            'message' => $message,
            'from' => 4546,
            'callback_url' => '',
        ])->body();
    }
}
