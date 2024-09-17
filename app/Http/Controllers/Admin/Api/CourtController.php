<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Booking;
use App\Models\Stadium;
use App\Traits\BookingTrait;
use App\Traits\PhotoTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    use PhotoTrait;
    use BookingTrait;

    public function getSchedule(Request $request)
    {
        $date = $request->query('date');
        $stadium = Stadium::query()->find($request->get('stadium'));

        // Fetch courts with schedules and bookings for the given date
        $courts = $stadium->courts()->with(['schedules', 'stadium'])->where('is_active', true)->where('sport_type_id', $request->get('sportTypeId'))
            ->get();

        $bookings = Booking::whereDate('date', $date)->where('status', 'paid')
            ->get();

        // Prepare the response data
        $response = [
            'courts' => $courts->map(function ($court) use ($date) {
                return [
                    'id' => $court->id,
                    'name' => $court->name,
                    'schedules' => $court->schedules->map(function ($schedule) {
                        return [
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time,
                            'cost' => $schedule->cost,
                        ];
                    }),
                ];
            }),
            'bookings' => $bookings->map(function ($booking) {
                return [
                    'date' => $booking->date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'court_id' => $booking->court_id,
                ];
            }),
        ];

        return response()->json($response);
    }

    public function show(Court $court): JsonResponse
    {
//        $schedules = $court->schedules()->where('is_booked', false);
//
//        if (!$schedules->exists()) {
//            return response()->json(['error' => 'Невозможно выбрать корт, так как не имеются расписание.'], 422);
//        } else {
//            return response()->json($schedules->get());
//        }

        return response()->json($court);
    }


    public function deletePhoto($photoPath, $id): JsonResponse
    {
        $court = Court::query()->findOrFail($id);

        $photosUrl = json_decode($court->photos);

        $urlId = array_search('court_photos/' . $photoPath, $photosUrl);

        if ($urlId !== false) {
            unset($photosUrl[$urlId]);
            $this->delete($court, $photosUrl, $photoPath, 'court_photos/');

            return response()->json(['message' => 'Photo deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Photo not found'], 404);
        }
    }

    public function isActive(Request $request, Court $court): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        if ($validated['is_active'] == 0 && $this->courtHasBookings($court)) {
            return response()->json(['error' => 'Невозможно деактивировать корт, так как имеются активные бронирования.'], 422);
        }

        $court->update(['is_active' => $validated['is_active']]);

        return response()->json([], 200);
    }
}
