<?php

namespace App\Services;

use App\Models\Day;
use App\Models\Hour;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function store(array $validated): Day
    {
        return DB::transaction(function () use ($validated) {
            $day = Day::query()->create($validated);

            foreach ($validated['hours'] as $hour) {
                $data = [
                    'start_time' => $hour['start_time'],
                    'end_time' => $hour['end_time'],
                    'day_id' => $day->id
                ];

                Hour::query()->create($data);
            }

            return $day;
        });
    }

    public function update(Day $day, array $validated): Day
    {
        return DB::transaction(function () use ($day, $validated) {
            $day->update(['date' => $validated['date']]);

            $day->hours()->delete();

            foreach ($validated['hours'] as $hour) {
                $data = [
                    'start_time' => $hour['start_time'],
                    'end_time' => $hour['end_time'],
                    'day_id' => $day->id
                ];

                Hour::query()->create($data);
            }

            return $day;
        });
    }

    public function destroy(Day $day): void
    {
        $day->delete();
    }
}
