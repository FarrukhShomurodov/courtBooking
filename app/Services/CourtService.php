<?php

namespace App\Services;

use App\Models\Court;
use App\Models\Schedule;
use App\Traits\PhotoTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourtService
{
    use PhotoTrait;

    public function store(array $validated): Builder|Model
    {
        if (isset($validated['photos'])) {
            $validated['photos'] = $this->storePhotos('court_photos');
        }

        return DB::transaction(function () use ($validated) {
            $court = Court::query()->create($validated);

            foreach ($validated['schedule'] as $schedule) {
                if ($schedule['cost'] && $schedule['cost'] != 0) {
                    $data = [
                        'court_id' => $court->id,
                        'cost' => $schedule['cost'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                    ];

                    Schedule::query()->create($data);
                }
            }

            return $court;
        });
    }

    public function update(Court $court, array $validated): Court
    {
        if (isset($validated['photos'])) {
            $validated['photos'] = $this->updatePhotoPaths($validated['photos'], 'court_photos', $court);
        }


        return DB::transaction(function () use ($court, $validated) {
            $court->update($validated);

            $court->schedules()->delete();

            foreach ($validated['schedule'] as $schedule) {
                if (isset($schedule['cost']) && $schedule['cost'] != 0) {
                    $data = [
                        'court_id' => $court->id,
                        'cost' => $schedule['cost'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                    ];

                    $court->schedules()->create($data);
                }
            }

            return $court;
        });
    }

    public function destroy(Court $court): void
    {

        if ($court->photos) {
            foreach (json_decode($court->photos) as $photo) {
                if (Storage::disk('public')->exists($photo)) {
                    Storage::disk('public')->delete($photo);
                }
            }
        }

        $court->delete();
    }
}
