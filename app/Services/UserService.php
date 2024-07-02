<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function store(array $validated): Model|Builder
    {
        $validated['password'] = Hash::make($validated['password']);
        $user = User::query()->create($validated);

        $role = Role::findById($validated['role_id']);
        $user->assignRole($role);

        return $user;
    }

    public function update(User $user, array $validated): User
    {
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            $validated['password'] = $user->password;
        }
        $user->update($validated);

        $role = Role::findById($validated['role_id']);
        $user->syncRoles($role);

        return $user->refresh();
    }

    public function destroy(User $user): void
    {
        $user->delete();
    }
}
