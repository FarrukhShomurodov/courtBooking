<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Services\BotUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotUserController extends Controller
{
    protected BotUserService $botUserService;

    public function __construct(BotUserService $botUserService)
    {
        $this->botUserService = $botUserService;
    }

    public function isActive(Request $request, BotUser $botUser): JsonResponse
    {
        $validated = $request->validate(['isactive' => 'required|boolean']);

        $this->botUserService->update($botUser, $validated);

        return response()->json([], 200);
    }
}
