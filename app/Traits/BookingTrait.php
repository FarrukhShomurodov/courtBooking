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
            foreach ($court->days as $day) {
                if ($day->date >= $currentDate && $day->hours()->where('is_booked', true)->exists()) {
                    return true;
                }
            }
        }
        return false;
    }

    private function courtHasBookings(Court $court): bool
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        foreach ($court->days as $day) {
            if ($day->date >= $currentDate && $day->hours()->where('is_booked', true)->exists()) {
                return true;
            }
        }
        return false;
    }
}
