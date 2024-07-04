<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class NotAdmin implements Rule
{
    public function passes($attribute, $value): bool
    {
        $roleId = Role::query()->where('name', 'admin')->value('id');

        return !DB::table('model_has_roles')
            ->where('model_type', 'App\Models\User')
            ->where('role_id', $roleId)
            ->where('model_id', $value)
            ->exists();
    }

    public function message(): string
    {
        return 'The selected :attribute cannot be an admin.';
    }
}
