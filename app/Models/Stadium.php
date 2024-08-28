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

    public function bookingStatistics()
    {
        $bookings = $this->courts()->with('bookings')->get()->pluck('bookings')->flatten();
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

}
