<?php

namespace App\Services;

use App\Models\Court;
use App\Traits\PhotoTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CourtService
{
    use PhotoTrait;

    public function store(array $validated): Builder|Model
    {
        if (isset($validated['photos'])) {
            $validated['photos'] = $this->storePhotos('court_photos');
        }

        return Court::query()->create($validated);
    }

    public function update(Court $court, array $validated): Court
    {
        if (isset($validated['photos'])) {
            $validated['photos'] = $this->updatePhotoPaths($validated['photos'], 'court_photos', $court);
        }

        $court->update($validated);

        return $court->refresh();

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
