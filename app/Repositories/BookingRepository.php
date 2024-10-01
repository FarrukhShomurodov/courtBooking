<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\BookingItem;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    public function byDate($date): Collection|array
    {
        return BookingItem::query()->where('date', $date)->where('status','paid')->get();
    }
}
