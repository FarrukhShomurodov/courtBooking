<?php

namespace App\Rules;

use App\Models\Coach;
use Illuminate\Contracts\Validation\Rule;

class CoachHasSportType implements Rule
{
    protected $sportTypeIds;

    public function __construct( $sportTypeIds)
    {
        $this->sportTypeIds = $sportTypeIds;
    }

    public function passes($attribute, $value): bool
    {
        $coach = Coach::query()->find($value);

        if ($coach) {
            return $coach->sportTypes->pluck('id')->intersect($this->sportTypeIds)->isNotEmpty();
        }

        return false;
    }

    public function message(): string
    {
        return "У тренера нет указанного вида спорта.";
    }
}
