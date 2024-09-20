<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\SportType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function store(array $validated): Model|Builder
    {
        return DB::transaction(function () use ($validated) {
            $validated['password'] = Hash::make($validated['password']);

            if (isset($validated['avatar'])) {
                $path = $validated['avatar']->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            $user = User::query()->create($validated);
            $role = Role::findById($validated['role_id']);
            $user->assignRole($role);

            if ($validated['role_id'] == 3) {
                $coach = new Coach();
                $coach->user_id = $user->id;
                $coach->price_per_hour = $validated['price_for_coach'];
                $coach->description = $validated['description'];

                $newSportTypes = [];
                foreach ($validated['sport_types'] as $sportType) {
                    if (is_numeric($sportType)) {
                        $existingSportType = SportType::query()->find($sportType);
                    } else {
                        $existingSportType = SportType::query()->where('name', $sportType)->first();
                    }

                    if (!$existingSportType) {
                        $newSportType = SportType::query()->create(['name' => $sportType]);
                        $newSportTypes[] = $newSportType->id;
                    } else {
                        $newSportTypes[] = $existingSportType->id;
                    }
                }

                $coach->save();
                $coach->sportTypes()->sync($newSportTypes);
            }

            return $user;
        });
    }

    public function update(User $user, array $validated): User
    {
        return DB::transaction(function () use ($user, $validated) {
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                $validated['password'] = $user->password;
            }

            if (isset($validated['avatar'])) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $path = $validated['avatar']->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            $user->update($validated);

            $role = Role::findById($validated['role_id']);
            $user->syncRoles($role);

            if ($validated['role_id'] == 3) {
                $coach = Coach::query()->firstOrCreate(['user_id' => $user->id]);
                $coach->price_per_hour = $validated['price_for_coach'];
                $coach->description = $validated['description'];

                $newSportTypes = [];
                foreach ($validated['sport_types'] as $sportType) {
                    if (is_numeric($sportType)) {
                        $existingSportType = SportType::query()->find($sportType);
                    } else {
                        $existingSportType = SportType::query()->where('name', $sportType)->first();
                    }

                    if (!$existingSportType) {
                        $newSportType = SportType::query()->create(['name' => $sportType]);
                        $newSportTypes[] = $newSportType->id;
                    } else {
                        $newSportTypes[] = $existingSportType->id;
                    }
                }

                $coach->save();
                $coach->sportTypes()->sync($newSportTypes);
            }

            return $user->refresh();
        });
    }

    public function destroy(User $user): void
    {
        DB::transaction(function () use ($user) {
            if ($user->avatar) {
                if (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }

            if ($user->hasRole('Coach')) {
                $user->coach()->delete();
            }

            $user->delete();
        });
    }
}
