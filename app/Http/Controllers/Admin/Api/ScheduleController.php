<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Telegram\PaycomController;
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
        $bookings = $court->bookings()->where('date', $validated['date'])->where('status', 'paid')->get();
        $schedules = $court->schedules;

        if ($schedules->isEmpty()) {
            return response()->json(['message' => __('errors.cannot_select_cort_due_to_has_schedule')], 422);
        }

        $availableSchedules = $this->filterAvailableSchedules($schedules, $bookings, Carbon::parse($validated['date']));

        return response()->json($availableSchedules);
    }

    public function priceByTime(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) use ($request) {
                    $startTime = $request->input('start_time');
                    $endTime = $value;
                    if ($endTime === '00:00:00') {
                        $endTime = '24:00:00';
                    }

                    if (strtotime($startTime) >= strtotime($endTime)) {
                        $fail(trans('validation.time_after'));
                    }
                }
            ],
        ]);


        $courtId = $validated['court_id'];
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $scheduleExists = Schedule::query()
            ->where('court_id', $courtId)
            ->where('start_time', '<=', $startTime)
            ->exists();

        if (!$scheduleExists) {
            return response()->json(['message' => __('errors.no_schedule_found_with_the_given_start_time')], 422);
        }

        $schedules = Schedule::query()
            ->where('court_id', $courtId)
            ->get();

        $totalCost = 0;

        foreach ($schedules as $schedule) {
            $scheduleStartTime = Carbon::parse($schedule->start_time);
            $scheduleEndTime = Carbon::parse($schedule->end_time);

            if ($validated['end_time'] == "00:00:00") {
                $endTime = Carbon::parse($validated['end_time'])->addDay();

                if ($scheduleStartTime < $endTime && $scheduleEndTime >= $startTime) {
                    $totalCost += $schedule->cost;
                }
            }else{
                if ($scheduleStartTime < $endTime && $scheduleEndTime > $startTime) {
                    $totalCost += $schedule->cost;
                }
            }

        }

        return response()->json(['total_cost' => $totalCost]);
    }
}
