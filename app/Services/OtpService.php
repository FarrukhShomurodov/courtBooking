<?php

namespace App\Services;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function getToken()
    {
        try {
            $res = Http::timeout(30)->post('notify.eskiz.uz/api/auth/login', [
                'email' => 'volmir.kim01@gmail.com',
                'password' => 'lxIk91uC6ESSoOgtmzmFNkqhqZa4dCuBYu259ClY'
            ]);

            if ($res->ok() && isset($res['data']['token'])) {
                return $res['data']['token'];
            } else {
                Log::error('Ошибка аутентификации на Eskiz', ['response' => $res->body()]);
                abort(400, 'Authentication failed');
            }
        } catch (ConnectException $e) {
            Log::error('Ошибка соединения: ' . $e->getMessage());
            return false;
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return false;
        }
    }

    public function sendMessage($phoneNumber, $message): string
    {
        try {
            $response = Http::timeout(30)
                ->withToken($this->getToken())
                ->post('https://notify.eskiz.uz/api/message/sms/send', [
                    'mobile_phone' => $phoneNumber,
                    'message' => $message,
                    'from' => 4546,
                    'callback_url' => '',
                ]);

            return true;
        } catch (ConnectException $e) {
            Log::error('Ошибка соединения: ' . $e->getMessage());
            return false;
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return false;
        }
    }
}
