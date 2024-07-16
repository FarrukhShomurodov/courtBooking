<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'start_time',
        'end_time',
        'cost',
    ];

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }
}
