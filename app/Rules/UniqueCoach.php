<?php

namespace App\Rules;

use App\Models\Stadium;
use Illuminate\Contracts\Validation\Rule;

class UniqueCoach implements Rule
{
    public function passes($attribute, $value)
    {
        return !Stadium::query()->where('coach_id', $value)->exists();
    }

    public function message()
    {
        return 'У тренера уже есть стадион.';
    }
}
