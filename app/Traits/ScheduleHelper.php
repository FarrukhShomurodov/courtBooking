<?php

namespace App\Traits;

use App\Models\Court;
use App\Models\Schedule;
use Carbon\Carbon;

trait ScheduleHelper
{
    public function checkCourtAvailability($courtId, $startTime, $endTime, $date): bool
    {
        return Court::query()->find($courtId)
            ->bookings()
            ->where('date', $date)
            ->where('status', 'paid')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $startTime)
                            ->where('end_time', '>', $endTime);
                    });
            })
            ->exists();
    }

    public function filterAvailableSchedules($schedules, $bookings)
    {
        return $schedules->filter(function ($schedule) use ($bookings) {
            foreach ($bookings as $booking) {
                $bookingStartTime = Carbon::parse($booking->start_time);
                $bookingEndTime = Carbon::parse($booking->end_time);
                $scheduleStartTime = Carbon::parse($schedule->start_time);
                $scheduleEndTime = Carbon::parse($schedule->end_time);

                if ($scheduleStartTime->between($bookingStartTime, $bookingEndTime) ||
                    $scheduleEndTime->between($bookingStartTime, $bookingEndTime) ||
                    $bookingStartTime->between($scheduleStartTime, $scheduleEndTime) ||
                    $bookingEndTime->between($scheduleStartTime, $scheduleEndTime)) {
                    return false;
                }
            }
            return true;
        });
    }
}
