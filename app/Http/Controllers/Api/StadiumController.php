<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stadium;
use App\Traits\PhotoTrait;
use Illuminate\Http\JsonResponse;

class StadiumController extends Controller
{
    use PhotoTrait;

    public function deletePhoto($photoPath, $id): JsonResponse
    {
        $stadium = Stadium::query()->findOrFail($id);

        $photosUrl = json_decode($stadium->photos);

        $urlId = array_search('stadium_photos/' . $photoPath, $photosUrl);

        if ($urlId !== false) {
            unset($photosUrl[$urlId]);
            $this->delete($stadium, $photosUrl, $photoPath, 'stadium_photos/');

            return response()->json(['message' => 'Photo deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Photo not found'], 404);
        }
    }
}
