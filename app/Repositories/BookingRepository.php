<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    public function byDate($date): Collection|array
    {
        return Booking::query()->where('date', $date)->get();
    }
}
