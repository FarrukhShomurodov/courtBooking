<?php

namespace App\Repositories;


use App\Models\BotUser;
use App\Models\Court;
use App\Models\Stadium;
use App\Models\User;

class StatisticsRepositories
{
    public function statics(): array
    {
        $userCount = User::query()->count();
        $botUserCount = BotUser::query()->count();
        $stadiumsCount = Stadium::query()->count();
        $courtCount = Court::query()->count();

        return [
            'user_count' => $userCount,
            'bot_user_count' => $botUserCount,
            'total_user_count' => $botUserCount + $userCount,
            'stadium_count' => $stadiumsCount,
            'court_count' => $courtCount
        ];
    }
}
