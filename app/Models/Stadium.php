<?php

namespace App\Models;

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

    public function bookingStatistics($date)
    {
        $bookings = $this->courts()->with(['bookings' => function ($query) use ($date) {
            if ($date) {
                $query->whereDate('date', $date);
            }
        }])->get()->pluck('bookings')->flatten();

        $totalHours = $bookings->sum(function ($booking) {
            return $booking->getHours();
        });

        $totalRevenue = $bookings->sum('price');
        $manualRevenue = $bookings->where('source', 'manual')->sum('price');
        $botRevenue = $totalRevenue - $manualRevenue;

        return [
            'total_hours' => $totalHours,
            'manual_hours' => $bookings->where('source', 'manual')->sum(function ($booking) {
                return $booking->getHours();
            }),
            'bot_hours' => $bookings->where('source', 'bot')->sum(function ($booking) {
                return $booking->getHours();
            }),
            'total_revenue' => $totalRevenue,
            'manual_revenue' => $manualRevenue,
            'bot_revenue' => $botRevenue,
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
