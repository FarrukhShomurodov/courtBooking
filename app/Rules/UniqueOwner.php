<?php

namespace App\Rules;

use App\Models\Stadium;
use Illuminate\Contracts\Validation\Rule;

class UniqueOwner implements Rule
{

    protected $stadiumId;

    public function __construct($stadiumId = null)
    {
        $this->stadiumId = $stadiumId;
    }

    public function passes($attribute, $value): bool
    {
        $query = Stadium::query()->where('owner_id', $value);

        if ($this->stadiumId) {
            $query->where('id', '!=', $this->stadiumId);
        }

        return !$query->exists();
    }

    public function message(): string
    {
        return 'У этого пользователя уже есть стадион.';
    }
}
