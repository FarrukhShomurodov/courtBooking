<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Day;
use App\Models\Hour;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function getDaysByCourt(Court $court): JsonResponse
    {
        $days = Day::query()->where('court_id', $court->id)->get();
        return response()->json($days);
    }

    public function getHoursByDay(Day $day): JsonResponse
    {
        $hours = Hour::query()->where('day_id', $day->id)->where('is_booked', false)->get();
        return response()->json($hours);
    }
}
