<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stadium extends Model
{
    use HasFactory;

    protected $table = 'stadiums';

    protected $fillable = [
        'name',
        'description',
        'address',
        'map_link',
        'photos',
        'is_active',
        'coach_id',
        'manager_id',
        'owner_id',
    ];

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function sportTypes(): BelongsToMany
    {
        return $this->belongsToMany(SportType::class, 'sport_stadium');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'coach_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function bookingStatistics($dateFrom = null, $dateTo = null)
    {
        // Получаем все бронирования в заданный период
        $bookings = $this->courts()->with(['bookings' => function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('date', '<', $dateTo);
            }

            $query->where('status', 'paid');

        }])->get()->pluck('bookings')->flatten();

        $totalHoursBooked = $bookings->where('status', 'paid')->count();

        $unbookedHours = $this->courts()->get()->reduce(function ($carry, $court) use ($dateFrom, $dateTo) {
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
            $schedulesCount = $court->schedules()->count();

            $timeFormat = 24 * $interval;
            $unActiveHour = $timeFormat - ($schedulesCount * $interval);

            $unbookedHours = ($timeFormat - $unActiveHour);

            return $carry + $unbookedHours;
        }, 0) - $totalHoursBooked;

        $unActiveHour = $this->courts()->get()->reduce(function ($carry, $court) use ($dateFrom, $dateTo) {
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
            $schedulesCount = $court->schedules()->count();

            $timeFormat = 24 * $interval;
            return $timeFormat - ($schedulesCount * $interval);
        }, 0);

        // Рассчитываем общую выручку и разделение на ручные и бот-бронирования
        $totalRevenue = $bookings->sum('price');
        $manualRevenue = $bookings->where('source', 'manual')->sum('price');
        $botRevenue = $bookings->where('source', 'bot')->where('status', 'paid')->sum('price');

        // Возвращаем данные
        return [
            'total_hours_booked' => $totalHoursBooked,
            'manual_hours' => $bookings->where('source', 'manual')->count(),
            'bot_hours' => $bookings->where('source', 'bot')->count(),
            'total_revenue' => $totalRevenue,
            'manual_revenue' => $manualRevenue,
            'bot_revenue' => $botRevenue,
            'unbooked_hours' => $unbookedHours,
            'un_active_hours' => $unActiveHour,
        ];
    }


    public function getMinimumCourtCost(): ?int
    {
        // Получаем минимальную стоимость из всех кортов стадиона
        return $this->courts->map(function ($court) {
            return $court->getMinimumCost();
        })->filter()->min();
    }

    public function filterCourts($date = null, $startTime = null, $endTime = null)
    {
        // Получаем все активные корты стадиона
        $courts = $this->courts()->where('is_active', true)
            ->with('sportTypes')
            ->with('stadium')
            ->with(['bookings' => function ($query) use ($date, $startTime, $endTime) {
                if ($date) {
                    $query->where('date', $date)
                        ->where('status', 'paid');
                }

                if ($startTime && $endTime) {
                    $query->where(function ($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhereBetween('end_time', [$startTime, $endTime])
                            ->orWhere(function ($query) use ($startTime, $endTime) {
                                $query->where('start_time', '<', $startTime)
                                    ->where('end_time', '>', $endTime);
                            });
                    });
                }
            }])
            ->get();

        // Проверяем, есть ли у какого-либо корта свободное время
        $availableCourts = $courts->filter(function ($court) {
            return $court->bookings->isEmpty();
        });

        // Если есть свободные корты, возвращаем их
        if ($availableCourts->isNotEmpty()) {
            return $availableCourts;
        }

        // Если свободных кортов нет, возвращаем все корты без фильтрации
        return $courts;
    }

}
