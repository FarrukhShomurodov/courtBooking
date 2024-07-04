<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hour extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'day_id',
        'is_booked'
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class);
    }
}
