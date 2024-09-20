<?php

namespace App\Http\Controllers\Telegram\Api;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UserController extends Controller
{
    public function getUserByChatId($chat_id): JsonResponse
    {
        $botUser = BotUser::query()->where('chat_id', $chat_id)->first();

        if ($botUser) {
            return response()->json([
                'success' => true,
                'data' => $botUser
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
    }

    public function hasUser(Request $request): JsonResponse
    {
        $userData = $request->all();
        $userId = $userData['user']['id'] ?? null;

        $user = BotUser::query()->where('chat_id', $userId)->first();

        if ($user) {
            App::setLocale($user->lang);
            session(['locale' => $user->lang]);

            return response()->json([
                'exists' => true,
                'isactive' => $user->isactive == 1
            ], 200);
        } else {
            return response()->json(['exists' => false], 200);
        }
    }

}
