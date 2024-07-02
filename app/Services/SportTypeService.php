<?php

namespace App\Services;

use App\Models\SportType;
use App\Traits\PhotoTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SportTypeService
{
    use PhotoTrait;

    public function store(array $validated): Model|Builder
    {
        $validated['photos'] = $this->storePhotos($validated, 'sport_type_photos');

        return SportType::query()->create($validated);
    }

    public function update(SportType $sportType, array $validated): SportType
    {
        $validated['photos'] = $this->updatePhotoPaths($validated['photos'], 'sport_type_photos', $sportType);

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
