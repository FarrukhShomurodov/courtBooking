<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'user_id',
        'full_name',
        'phone_number',
        'date',
        'price',
        'start_time',
        'end_time',
        'source',
        'status',
    ];

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getHours(): float|int
    {
        return (strtotime($this->end_time) - strtotime($this->start_time)) / 3600;
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
