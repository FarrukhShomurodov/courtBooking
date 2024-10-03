<?php

namespace App\Traits;

use App\Models\Court;
use Carbon\Carbon;

trait ScheduleHelper
{
    public function checkCourtAvailability($courtId, $startTime, $endTime, $date): bool
    {
        $schedules = Court::query()->find($courtId)->schedules()->get();
        $bookedSchedules = Court::query()->find($courtId)->bookings()
            ->where('date', $date)
            ->where('status', 'paid')
            ->get(['start_time', 'end_time']);

        $availableSchedules = $this->filterAvailableSchedules($schedules, $bookedSchedules);

        $sortedSchedules = $availableSchedules->sortBy('start_time')->values();

        $isExactSlotAvailable = $this->isExactSlotAvailable($sortedSchedules, $startTime, $endTime);

        return !$isExactSlotAvailable;
    }

    private function isExactSlotAvailable($schedules, $startTime, $endTime): bool
    {
        foreach ($schedules as $schedule) {
            $scheduleStart = Carbon::parse($schedule->start_time);
            $scheduleEnd = Carbon::parse($schedule->end_time);

            if ($scheduleStart->equalTo($startTime) && $scheduleEnd->equalTo($endTime)) {
                return true;
            }
        }

        return false;
    }


    public function filterAvailableSchedules($schedules, $bookings, $date)
    {
        $now = Carbon::now();
        return $schedules->filter(function ($schedule) use ($bookings, $now, $date) {
            $scheduleStartTime = Carbon::parse($schedule->start_time);

            if ($date->isToday() && $scheduleStartTime->lessThanOrEqualTo($now->format('H:i:s'))) {
                return false;
            }

            foreach ($bookings as $booking) {
                $bookingStartTime = Carbon::parse($booking->start_time);
                $bookingEndTime = Carbon::parse($booking->end_time);

                $scheduleStartTime = Carbon::parse($schedule->start_time);
                $scheduleEndTime = Carbon::parse($schedule->end_time);

                if ($scheduleStartTime->equalTo($bookingStartTime) ||
                    $scheduleEndTime->equalTo($bookingEndTime) ||
                    $bookingStartTime->equalTo($scheduleStartTime) ||
                    $bookingEndTime->equalTo($scheduleEndTime)) {
                    return false;
                }
            }
            return true;
        });
    }
}
