<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function store(array $validated)
    {
        $courtId = $validated['court_id'];
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);
        $date = $validated['date'];
        $isAvailable = $this->checkCourtAvailability($courtId, $startTime, $endTime, $date);

        if ($isAvailable) {
            return back()->withErrors('В указанное время корт недоступен.');
        }

        return DB::transaction(function () use ($validated, $startTime, $endTime, $courtId) {
            $booking = Booking::query()->create([
                'court_id' => $courtId,
                'user_id' => $validated['user_id'],
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'date' => $validated['date'],
                'price' => $validated['price'],
                'start_time' => $startTime->toTimeString(),
                'end_time' => $endTime->toTimeString(),
            ]);

            $this->updateCourtSchedule($courtId, $startTime, $endTime);

            return $booking;
        });
    }

    protected function checkCourtAvailability($courtId, $startTime, $endTime, $date): int
    {
        return Court::find($courtId)
            ->bookings()
            ->where('date', $date)
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


    protected function updateCourtSchedule($courtId, $startTime, $endTime): void
    {
        $schedules = Schedule::query()->where('court_id', $courtId)
            ->where('start_time', '>=', $startTime)
            ->where('end_time', '<=', $endTime)
            ->get();

        foreach ($schedules as $schedule) {
            $schedule->update(['is_booked' => true]);
        }
    }
}
