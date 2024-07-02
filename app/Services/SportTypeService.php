<?php

namespace App\Services;

use App\Models\SportType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SportTypeService
{
    public function store(array $validated): Model|Builder
    {
        if (isset($validated['photos'])) {

            $photos = array_map(function ($file) {
                return $file->store('sport_type_photos', 'public');
            }, request()->file('photos'));

            $validated['photos'] = json_encode($photos);
        }

        return SportType::query()->create($validated);
    }

    public function update(SportType $sportType, array $validated): SportType
    {
        if (isset($validated['photos'])) {
            $photos = $validated['photos'];
            $uploadedPhotos = [];

            foreach ($photos as $photo) {
                $path = $photo->store('sport_type_photos', 'public');
                $uploadedPhotos[] = $path;
            }

            $existingPhotos = json_decode($sportType->photos) ?: [];
            $allPhotos = array_merge($existingPhotos, $uploadedPhotos);
            $validated['photos'] = json_encode($allPhotos);
        }


        $sportType->update($validated);

        return $sportType->refresh();

    }

    public function destroy(SportType $sportType): void
    {

        foreach (json_decode($sportType->photos) as $photo) {
            if (Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->delete($photo);
            }
        }

        $sportType->delete();
    }
}
