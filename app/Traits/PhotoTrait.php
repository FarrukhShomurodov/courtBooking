<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait PhotoTrait
{
    protected function storePhotos(array $validated, string $storagePath): string
    {
        if (isset($validated['photos'])) {
            $photos = array_map(function ($file) use ($storagePath) {
                return $file->store($storagePath, 'public');
            }, request()->file('photos'));

            $validated['photos'] = json_encode($photos);
        }

        return $validated['photos'];
    }

    public function updatePhotoPaths(array $validated, string $storagePath, $model): bool|string|null
    {
        if (isset($validated)) {
            $photos = $validated;
            $uploadedPhotos = [];

            foreach ($photos as $photo) {
                $path = $photo->store($storagePath, 'public');
                $uploadedPhotos[] = $path;
            }

            $existingPhotos = json_decode($model->photos) ?: [];
            $allPhotos = array_merge($existingPhotos, $uploadedPhotos);
            return json_encode($allPhotos);
        } else {
            return null;
        }
    }

    public function delete($model, $photosUrl, string $photoPath, string $storagePath): void
    {
        $updatedPhotosUrl = json_encode(array_values($photosUrl));

        $model->update(['photos' => $updatedPhotosUrl]);

        Storage::disk('public')->delete($storagePath . $photoPath);
    }
}
