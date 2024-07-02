<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SportType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SportTypeController extends Controller
{
    public function deletePhoto($photoPath, $id): JsonResponse
    {
        $sportType = SportType::query()->findOrFail($id);

        $photosUrl = json_decode($sportType->photos);

        $urlId = array_search('sport_type_photos/' . $photoPath, $photosUrl);

        if ($urlId !== false) {
            unset($photosUrl[$urlId]);

            $updatedPhotosUrl = json_encode(array_values($photosUrl));

            $sportType->update(['photos' => $updatedPhotosUrl]);

            Storage::disk('public')->delete('sport_type_photos/' . $photoPath);

            return response()->json(['message' => 'Photo deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Photo not found'], 404);
        }
    }
}
