<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stadium;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class StadiumController extends Controller
{
    public function deletePhoto($photoPath, $id): JsonResponse
    {
        $stadium = Stadium::query()->findOrFail($id);

        $photosUrl = json_decode($stadium->photos);

        $urlId = array_search('stadium_photos/' . $photoPath, $photosUrl);

        if ($urlId !== false) {
            unset($photosUrl[$urlId]);

            $updatedPhotosUrl = json_encode(array_values($photosUrl));

            $stadium->update(['photos' => $updatedPhotosUrl]);

            Storage::disk('public')->delete('stadium_photos/' . $photoPath);

            return response()->json(['message' => 'Photo deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Photo not found'], 404);
        }
    }
}
