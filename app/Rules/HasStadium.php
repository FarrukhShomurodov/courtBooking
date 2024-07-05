<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;
use Spatie\Permission\Models\Role;

class HasStadium implements Rule
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function passes($attribute, $value): bool
    {
        $role = Role::findById($value);
        $user = User::query()->find($this->userId);

        if ($user && ($user->stadiumOwner()->exists() || $user->stadiumTrainer()->exists())) {
            if ($role->name == 'owner stadium' || $role->name == 'trainer') {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return 'Вы не можете изменить роль, если он привязан к стадиону.';
    }
}
