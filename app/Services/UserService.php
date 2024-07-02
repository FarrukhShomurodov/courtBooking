<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function store(array $validated): Model|Builder
    {
        $validated['password'] = Hash::make($validated['password']);
        return User::query()->create($validated);
    }

    public function update(User $user, array $validated): User
    {
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            $validated['password'] = $user->password;
        }
        $user->update($validated);
        return $user->refresh();
    }

    public function destroy(User $user): void
    {
        $user->delete();
    }
}
