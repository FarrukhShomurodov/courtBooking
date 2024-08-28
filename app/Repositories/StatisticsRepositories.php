<?php

namespace App\Repositories;


use App\Models\Booking;
use App\Models\BotUser;
use App\Models\Court;
use App\Models\SportType;
use App\Models\Stadium;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatisticsRepositories
{
    public function adminStatistics(): array
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
            'most_booking_date' => $mostBookedDate->date ?? __('dashboard.book_not_found'),
            'most_booked_time_slot' => $mostBookedTimeSlot ?? __('dashboard.book_not_found')
        ];
    }

    public function stadiumOwnerStatistics(): array
    {
        $stadium = Auth::user()->stadiumOwner()->first();

        if (!$stadium) {
            return [];
        }

        $courtCount = $stadium->courts()->count();
        $bookingCount = Booking::query()->whereIn('court_id', $stadium->courts()->pluck('id'))->count();
        $mostBookedDate = Booking::query()
            ->whereIn('court_id', $stadium->courts()->pluck('id'))
            ->select('date', DB::raw('count(*) as booking_count'))
            ->groupBy('date')
            ->orderBy('booking_count', 'desc')
            ->first();
        $mostBookedTimeSlot = Booking::query()
            ->whereIn('court_id', $stadium->courts()->pluck('id'))
            ->select('start_time', 'end_time', DB::raw('count(*) as booking_count'))
            ->groupBy('start_time', 'end_time')
            ->orderBy('booking_count', 'desc')
            ->first();

        $sportTypeCount = $stadium->sportTypes()->count();

        return [
            'stadium_count' => 1,
            'stadium_name' => $stadium->name,
            'court_count' => $courtCount,
            'booking_count' => $bookingCount,
            'sport_type_count' => $sportTypeCount,
            'most_booking_date' => $mostBookedDate->date ?? __('dashboard.book_not_found'),
            'most_booked_time_slot' => $mostBookedTimeSlot ?? __('dashboard.book_not_found')
        ];
    }

    public function stadiumStatistics(Stadium $stadium)
    {
        $statistics = $stadium->bookingStatistics();

        return [
            'bot_book_count' => $statistics['bot_hours'],
            'manual_book_count' => $statistics['manual_hours'],
            'total_book_count' => $statistics['total_hours'],
            'bot_revenue' => $statistics['bot_revenue'],
            'manual_revenue' => $statistics['manual_revenue'],
            'total_revenue' => $statistics['total_revenue'],
        ];
    }

    public function courtStatistics(Court $court): array
    {
        $bookCountFromBot = $court->bookings()->where('source', 'bot')->count();
        $bookCountFromManual = $court->bookings()->where('source', 'manual')->count();
        $totalBookCount = $bookCountFromBot + $bookCountFromManual;

        $botRevenue = $court->bookings()->where('source', 'bot')->sum('price');
        $manualRevenue = $court->bookings()->where('source', 'manual')->sum('price');
        $totalRevenue = $botRevenue + $manualRevenue;

        return [
            'bot_book_count' => $bookCountFromBot,
            'manual_book_count' => $bookCountFromManual,
            'total_book_count' => $totalBookCount,
            'bot_revenue' => $botRevenue,
            'manual_revenue' => $manualRevenue,
            'total_revenue' => $totalRevenue,
        ];
    }

    public function sportTypeStatistics(SportType $sportType): array
    {
        // Initialize statistics array
        $statistics = [
            'total_bookings' => 0,
            'total_revenue' => 0,
            'manual_revenue' => 0,
            'bot_revenue' => 0,
            'most_booked_date' => null,
            'most_booked_time_slot' => null,
        ];

        // Loop through each court related to the sport type
        foreach ($sportType->courts as $court) {
            $bookings = $court->bookings;

            // Calculate total bookings and revenue
            $statistics['total_bookings'] += $bookings->count();
            $statistics['total_revenue'] += $bookings->sum('price');
            $statistics['manual_revenue'] += $bookings->where('source', 'manual')->sum('price');
            $statistics['bot_revenue'] += $bookings->where('source', 'bot')->sum('price');

            // Calculate the most booked date
            $mostBookedDate = $bookings->groupBy('date')
                ->sortByDesc(function ($dateGroup) {
                    return $dateGroup->count();
                })
                ->keys()
                ->first();

            $statistics['most_booked_date'] = $mostBookedDate ?: null;

            // Calculate the most booked time slot
            $mostBookedTimeSlot = $bookings->groupBy(function ($booking) {
                return $booking->start_time . '-' . $booking->end_time;
            })
                ->sortByDesc(function ($timeSlotGroup) {
                    return $timeSlotGroup->count();
                })
                ->first();

            if ($mostBookedTimeSlot) {
                $firstBooking = $mostBookedTimeSlot->first();
                $statistics['most_booked_time_slot'] = [
                    'start_time' => $firstBooking->start_time,
                    'end_time' => $firstBooking->end_time,
                ];
            } else {
                $statistics['most_booked_time_slot'] = null;
            }
        }

        return $statistics;
    }
}
