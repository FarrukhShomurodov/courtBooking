<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function deletePhoto(User $user): JsonResponse
    {
        if ($user->avatar) {
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
                $user->avatar = null;
                $user->save();
                return response()->json(['message' => 'Photo deleted successfully'], 200);
            }
        }
        return response()->json(['message' => 'Photo not found'], 404);
    }
}
