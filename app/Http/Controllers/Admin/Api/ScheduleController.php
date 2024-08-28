<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Schedule;
use App\Traits\ScheduleHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    use ScheduleHelper;

    public function fetchByDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'court_id' => 'required|integer|exists:courts,id',
            'date' => 'required|date'
        ]);

        $court = Court::find($validated['court_id']);
        $bookings = $court->bookings()->where('date', $validated['date'])->get();
        $schedules = $court->schedules;

        if ($schedules->isEmpty()) {
            return response()->json(['message' => 'Невозможно выбрать корт, так как не имеются расписание.'], 422);
        }

        $availableSchedules = $this->filterAvailableSchedules($schedules, $bookings);

        return response()->json($availableSchedules);
    }

    public function priceByTime(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
        ]);

        $courtId = $validated['court_id'];
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $scheduleExists = Schedule::query()
            ->where('court_id', $courtId)
            ->where('start_time', '<=', $startTime)
            ->exists();

        if (!$scheduleExists) {
            return response()->json(['message' => 'No schedule found with the given start time'], 422);
        }

        $schedules = Schedule::query()
            ->where('court_id', $courtId)
            ->get();

        $totalCost = 0;

        foreach ($schedules as $schedule) {
            $scheduleStartTime = Carbon::parse($schedule->start_time);
            $scheduleEndTime = Carbon::parse($schedule->end_time);

            if ($scheduleStartTime < $endTime && $scheduleEndTime > $startTime) {
                $totalCost += $schedule->cost;
            }
        }

        return response()->json(['total_cost' => $totalCost]);
    }
}
