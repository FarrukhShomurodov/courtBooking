<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stadium;
use App\Traits\PhotoTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function isActive(Request $request, Stadium $stadium): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $stadium->update(['is_active' => $validated['is_active']]);

        return response()->json([], 200);
    }
}
