<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\SportType;
use App\Models\Stadium;
use App\Traits\PhotoTrait;
use Illuminate\Http\JsonResponse;

class SportTypeController extends Controller
{
    use PhotoTrait;

    public function deletePhoto($photoPath, $id): JsonResponse
    {
        $sportType = SportType::query()->findOrFail($id);

        $photosUrl = json_decode($sportType->photos);

        $urlId = array_search('sport_type_photos/' . $photoPath, $photosUrl);

        if ($urlId !== false) {
            unset($photosUrl[$urlId]);
            $this->delete($sportType, $photosUrl, $photoPath, 'sport_type_photos/');

            return response()->json(['message' => 'Photo deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Photo not found'], 404);
        }
    }

    public function byStadium(Stadium $stadium): JsonResponse
    {
        $sportTypes = $stadium->sportTypes()->get();
        return response()->json($sportTypes, 200);
    }
}
