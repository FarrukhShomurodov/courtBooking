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
        $botUser = BotUser::query()->where('chat_id', (string)$chat_id)->first();

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
        $userId = $request->input('chat_id') ?? null;

        $user = BotUser::query()->where('chat_id', (string)$userId)->first();

        if ($user) {
            return response()->json([
                'exists' => true,
                'lang' => $user->lang,
                'isactive' => $user->isactive == 1
            ], 200);
        } else {
            return response()->json(['exists' => false], 200);
        }
    }
}
