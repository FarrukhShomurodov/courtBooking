<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Day;
use App\Models\Hour;
use App\Models\User;

class BookingService
{
    public function store(array $validated)
    {
        $user = User::query()->find($validated['user_id']);
        Hour::query()->find($validated['hour_id'])->update(['is_booked' => true]);

        return $user->bookings()->create($validated);
    }

    public function destroy(Booking $booking): void
    {
        $booking->hour()->update(['is_booked' => false]);
        $booking->delete();
    }
}
