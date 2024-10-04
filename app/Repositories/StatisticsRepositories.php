<?php

namespace App\Repositories;


use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BotUser;
use App\Models\Court;
use App\Models\SportType;
use App\Models\Stadium;
use App\Models\User;
use Carbon\Carbon;
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
        $bookingCount = BookingItem::query()->count();
        $sportTypeCount = SportType::query()->count();
        $mostBookedDate = BookingItem::query()->select('date', DB::raw('count(*) as booking_count'))
            ->groupBy('date')
            ->orderBy('booking_count', 'desc')
            ->first();
        $mostBookedTimeSlot = BookingItem::query()->select('start_time', 'end_time', DB::raw('count(*) as booking_count'))
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
        $stadium = Auth::user()->stadiumOwner;

        if (!$stadium) {
            return [];
        }

        $courtCount = $stadium->courts()->count();
        $bookingCount = BookingItem::query()->whereIn('court_id', $stadium->courts()->pluck('id'))->count();
        $mostBookedDate = BookingItem::query()
            ->whereIn('court_id', $stadium->courts()->pluck('id'))
            ->select('date', DB::raw('count(*) as booking_count'))
            ->groupBy('date')
            ->orderBy('booking_count', 'desc')
            ->first();
        $mostBookedTimeSlot = BookingItem::query()
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

    public function stadiumStatistics(Stadium $stadium, $dateFrom = null, $dateTo = null)
    {
        $statistics = $stadium->bookingStatistics($dateFrom, $dateTo);

        return [
            'bot_book_count' => $statistics['bot_hours'],
            'manual_book_count' => $statistics['manual_hours'],
            'total_book_count' => $statistics['total_hours_booked'],
            'bot_revenue' => $statistics['bot_revenue'],
            'manual_revenue' => $statistics['manual_revenue'],
            'total_revenue' => $statistics['total_revenue'],
            'unbooked_hours' => $statistics['unbooked_hours'],
            'un_active_hours' => $statistics['un_active_hours'],
        ];
    }

    public function courtStatistics(Court $court, $dateFrom = null, $dateTo = null): array
    {
        // Получаем все бронирования в заданный период
        $bookings = $court->bookings()->when($dateFrom, function ($query, $dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        })->when($dateTo, function ($query, $dateTo) {
            $query->whereDate('date', '<', $dateTo);
        })->where('status', 'paid')->get();

        // Считаем забронированные часы
        $totalHoursBooked = $bookings->sum(function ($booking) {
            return $booking->getHours();
        });


        $schedulesCount = $court->schedules()->count();

        $from = Carbon::parse($dateFrom);
        $to = Carbon::parse($dateTo);

        if ($dateFrom && $dateTo) {
            $interval = $from->diffInDays($to);
        } elseif ($dateFrom) {
            $interval = $from->diffInDays(Carbon::now());
        } elseif ($dateTo) {
            $interval = Carbon::now()->diffInDays($to);
        } else {
            $interval = 1;
        }

        $interval = $interval === 0 ? 1 : $interval;

        $timeFormat = 24 * $interval;
        $unActiveHour = $timeFormat - ($schedulesCount * $interval);
        $unbookedHours = ($timeFormat - $unActiveHour) - ($totalHoursBooked);

        // Рассчитываем статистику по бронированиям
        $bookCountFromBot = $bookings->where('source', 'bot')->count();
        $bookCountFromManual = $bookings->where('source', 'manual')->count();
        $totalBookCount = $bookCountFromBot + $bookCountFromManual;

        $botRevenue = $bookings->where('source', 'bot')->sum('price');
        $manualRevenue = $bookings->where('source', 'manual')->sum('price');
        $totalRevenue = $botRevenue + $manualRevenue;

        return [
            'bot_book_count' => $bookCountFromBot,
            'manual_book_count' => $bookCountFromManual,
            'total_book_count' => $totalBookCount,
            'bot_revenue' => $botRevenue,
            'manual_revenue' => $manualRevenue,
            'total_revenue' => $totalRevenue,
            'unbooked_hours' => $unbookedHours,
            'un_active_hours' => $unActiveHour,
        ];
    }


    public function sportTypeStatistics(SportType $sportType, $stadiumId, $dateFrom = null, $dateTo = null): array
    {
        // Инициализация статистики
        $statistics = [
            'total_bookings' => 0,
            'total_revenue' => 0,
            'manual_revenue' => 0,
            'bot_revenue' => 0,
            'most_booked_date' => null,
            'most_booked_time_slot' => null,
            'unbooked_hours' => 0,
            'un_active_hours' => 0,
        ];

        if ($stadiumId === 'all') {
            $courts = Court::where('sport_type_id', $sportType->id)->get();
        } else {
            $courts = Court::where('stadium_id', $stadiumId)
                ->where('sport_type_id', $sportType->id)
                ->get();
        }

        // Сбор всех бронирований для подсчета незабронированных часов
        $allBookings = $courts->flatMap(function ($court) use ($dateFrom, $dateTo) {
            return $court->bookings()->where(function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'paid');
                if ($dateFrom) {
                    $query->whereDate('date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate('date', '<', $dateTo);
                }
            })->get();
        });

        // Подсчет забронированных часов
        $totalHoursBooked = $allBookings->sum(function ($booking) {
            return $booking->getHours();
        });

        $from = Carbon::parse($dateFrom);
        $to = Carbon::parse($dateTo);

        if ($dateFrom && $dateTo) {
            $interval = $from->diffInDays($to);
        } elseif ($dateFrom) {
            $interval = $from->diffInDays(Carbon::now());
        } elseif ($dateTo) {
            $interval = Carbon::now()->diffInDays($to);
        } else {
            $interval = 1;
        }

        $interval = $interval === 0 ? 1 : $interval;

        $schedules = $courts->flatMap(function ($court) {
            return $court->schedules()->get();
        });

        $timeFormat = 24 * $interval;
        $unActiveHour = $timeFormat - ($schedules->count() * $interval);

        $unbookedHours = ($timeFormat - $unActiveHour) - ($totalHoursBooked);

        $statistics['unbooked_hours'] = $unbookedHours;
        $statistics['un_active_hours'] = $unActiveHour;

        // Подсчет бронирований и доходов
        $statistics['total_bookings'] = $allBookings->count();
        $statistics['total_revenue'] = $allBookings->sum('price');
        $statistics['manual_revenue'] = $allBookings->where('source', 'manual')->sum('price');
        $statistics['bot_revenue'] = $allBookings->where('source', 'bot')->sum('price');

        // Подсчет наиболее бронируемой даты
        $mostBookedDate = $allBookings->groupBy('date')
            ->sortByDesc(function ($dateGroup) {
                return $dateGroup->count();
            })
            ->keys()
            ->first();

        $statistics['most_booked_date'] = $mostBookedDate ?: null;

        // Подсчет наиболее бронируемого временного интервала
        $mostBookedTimeSlot = $allBookings->groupBy(function ($booking) {
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

        return $statistics;
    }

}
