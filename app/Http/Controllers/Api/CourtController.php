<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Traits\PhotoTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    use PhotoTrait;

    public function show(Court $court): JsonResponse
    {
        $days = $court->days()->get()->load('hours');

        $res = [
            'court' => $court,
            'days' => $days,
        ];

        return response()->json($res);
    }

    public function deletePhoto($photoPath, $id): JsonResponse
    {
        $court = Court::query()->findOrFail($id);

        $photosUrl = json_decode($court->photos);

        $urlId = array_search('court_photos/' . $photoPath, $photosUrl);

        if ($urlId !== false) {
            unset($photosUrl[$urlId]);
            $this->delete($court, $photosUrl, $photoPath, 'court_photos/');

            return response()->json(['message' => 'Photo deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Photo not found'], 404);
        }
    }

    public function isActive(Request $request, Court $court): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $court->update(['is_active' => $validated['is_active']]);

        return response()->json([], 200);
    }
}
