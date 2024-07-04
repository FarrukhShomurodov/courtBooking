<?php

namespace App\Rules;

use App\Models\Stadium;
use Illuminate\Contracts\Validation\Rule;

class UniqueOwner implements Rule
{

    public function passes($attribute, $value)
    {
        return !Stadium::query()->where('owner_id', $value)->exists();
    }

    public function message()
    {
        return 'У владельца уже есть стадион.';
    }
}
