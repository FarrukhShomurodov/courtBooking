<?php

namespace App\Traits;

use App\Models\Court;
use App\Models\Stadium;
use Carbon\Carbon;

trait BookingTrait
{
    private function stadiumHasBookings(Stadium $stadium): bool
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        foreach ($stadium->courts as $court) {
            foreach ($court->bookings as $booking) {
                if ($booking->date >= $currentDate) {
                    return true;
                }
            }
        }
        return false;
    }

    private function courtHasBookings(Court $court): bool
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        foreach ($court->bookings as $booking) {
            if ($booking->date >= $currentDate) {
                return true;
            }
        }
        return false;
    }
}
