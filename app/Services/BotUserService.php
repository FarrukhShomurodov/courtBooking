<?php


namespace App\Services;

use App\Models\BotUser;

class BotUserService
{

    public function update(BotUser $botUser, array $validated): void
    {
        $botUser->update(['isactive' => $validated['isactive']]);
    }
}
