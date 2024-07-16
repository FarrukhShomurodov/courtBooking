<?php

namespace App\Repositories;


use App\Models\Booking;
use App\Models\BotUser;
use App\Models\Court;
use App\Models\SportType;
use App\Models\Stadium;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsRepositories
{
    public function statics(): array
    {
        $userCount = User::query()->count();
        $botUserCount = BotUser::query()->count();
        $stadiumsCount = Stadium::query()->count();
        $courtCount = Court::query()->count();
        $bookingCount = Booking::query()->count();
        $sportTypeCount = SportType::query()->count();
        $mostBookedDate = Booking::query()->select('date', DB::raw('count(*) as booking_count'))
            ->groupBy('date')
            ->orderBy('booking_count', 'desc')
            ->first();
        $mostBookedTimeSlot = Booking::query()->select('start_time', 'end_time', DB::raw('count(*) as booking_count'))
            ->groupBy('start_time', 'end_time')
            ->orderBy('booking_count', 'desc')
            ->first();

        return [
            'user_count' => $userCount,
            'bot_user_count' => $botUserCount,
            'total_user_count' => $botUserCount + $userCount,
            'stadium_count' => $stadiumsCount,
            'court_count' => $courtCount,
            'booking_count' => $bookingCount,
            'sport_type_count' => $sportTypeCount,
            'most_booking_date' => $mostBookedDate->date ?? 'Брони не найдены.',
            'most_booked_time_slot' => $mostBookedTimeSlot ?? 'Брони не найдены.'
        ];
    }
}
