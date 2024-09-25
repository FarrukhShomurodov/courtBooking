<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Traits\ScheduleHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    use ScheduleHelper;

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
                'user_id' => $validated['user_id'],
            ]);

            $booking->bookingItems()->create([
                'court_id' => $courtId,
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'date' => $validated['date'],
                'price' => $validated['price'] * 1000,
                'start_time' => $startTime->toTimeString(),
                'end_time' => $endTime->toTimeString(),
                'source' => $validated['source'],
                'status' => 'paid',
            ]);

            $booking->update([
                'total_price' => $validated['price'] * 1000,
            ]);

            return $booking;
        });
    }

    public function delete(BookingItem $booking): void
    {
        $booking->booking()->delete();
        $booking->delete();
    }
}
