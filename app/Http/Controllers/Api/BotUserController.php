<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotUserController extends Controller
{
    public function isActive(Request $request, BotUser $botUser): JsonResponse
    {
        $validated = $request->validate([
            'isactive' => 'required|boolean',
        ]);

        $botUser->update(['isactive' => $validated['isactive']]);

        return response()->json([], 200);
    }
}
