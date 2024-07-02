<?php

namespace App\Services;

use App\Models\Stadium;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StadiumService
{
    public function store(array $validated): Builder|Model
    {
        if (isset($validated['photos'])) {

            $photos = array_map(function ($file) {
                return $file->store('stadium_photos', 'public');
            }, request()->file('photos'));

            $validated['photos'] = json_encode($photos);
        }

        $stadium = Stadium::query()->create($validated);

        $stadium->owner()->first()->syncRoles('owner stadium');

        $stadium->sportTypes()->sync($validated['sport_types']);

        return $stadium;
    }

    public function update(Stadium $stadium, array $validated): Stadium
    {
        if (isset($validated['photos'])) {
            $photos = $validated['photos'];
            $uploadedPhotos = [];

            foreach ($photos as $photo) {
                $path = $photo->store('stadium_photos', 'public');
                $uploadedPhotos[] = $path;
            }

            $existingPhotos = json_decode($stadium->photos) ?: [];
            $allPhotos = array_merge($existingPhotos, $uploadedPhotos);
            $validated['photos'] = json_encode($allPhotos);
        }

        $stadium->update($validated);

        $stadium->owner()->first()->syncRoles('owner stadium');
        $stadium->sportTypes()->sync($validated['sport_types']);

        return $stadium->refresh();

    }

    public function destroy(Stadium $stadium): void
    {

        foreach (json_decode($stadium->photos) as $photo) {
            if (Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->delete($photo);
            }
        }

        $stadium->delete();
    }

}
