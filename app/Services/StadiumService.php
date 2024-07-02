<?php

namespace App\Services;

use App\Models\Stadium;
use App\Traits\PhotoTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StadiumService
{
    use PhotoTrait;

    public function store(array $validated): Builder|Model
    {
        $validated['photos'] = $this->storePhotos($validated, 'stadium_photos');

        $stadium = Stadium::query()->create($validated);

        $stadium->owner()->first()->syncRoles('owner stadium');

        $stadium->sportTypes()->sync($validated['sport_types']);

        return $stadium;
    }

    public function update(Stadium $stadium, array $validated): Stadium
    {
        $validated['photos'] = $this->updatePhotoPaths($validated['photos'], 'stadium_photos', $stadium);

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
