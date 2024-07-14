<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Traits\BookingTrait;
use App\Traits\PhotoTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    use PhotoTrait;
    use BookingTrait;

    public function show(Court $court): JsonResponse
    {
        $schedules = $court->schedules()->where('is_booked', false);

        if (!$schedules->exists()) {
            return response()->json(['error' => 'Невозможно выбрать корт, так как не имеются расписание.'], 422);
        } else {
            return response()->json($schedules->get());
        }

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
