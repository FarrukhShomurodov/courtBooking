<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking);
    }

    public function showByDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date'
        ]);

        $booking = Booking::query()->where('date', $validated['date'])->get();

        return response()->json($booking);

    }
}
