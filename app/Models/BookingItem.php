<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'booking_id',
        'full_name',
        'phone_number',
        'date',
        'price',
        'start_time',
        'end_time',
        'source',
        'status',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function getHours(): float|int
    {
        return (strtotime($this->end_time) - strtotime($this->start_time)) / 3600;
    }
}
