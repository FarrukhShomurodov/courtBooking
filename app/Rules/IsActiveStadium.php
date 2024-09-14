<?php

namespace App\Rules;

use App\Models\Stadium;
use Illuminate\Contracts\Validation\Rule;

class IsActiveStadium implements Rule
{
    protected $stadiumId;

    public function __construct($stadiumId = null)
    {
        $this->stadiumId = $stadiumId;
    }

    public function passes($attribute, $value): bool
    {
        $stadium = Stadium::find($this->stadiumId);

        // Проверяем, существует ли стадион и активен ли он
        return $stadium && $stadium->is_active;
    }

    public function message(): string
    {
        return 'Стадион не активный.';
    }
}
