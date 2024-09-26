<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HasStadium;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'second_name' => 'required|string|max:200',
            'login' => 'required|string|unique:users,login,' . $this->user->id,
            'role_id' => ['required', 'exists:roles,id', new HasStadium($this->user->id)],
            'avatar' => 'nullable|image|mimes:jpg,png',
            'password' => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->sometimes('price_for_coach', 'required|numeric', function ($input) {
            return $input->role_id == 3;
        });

        $validator->sometimes('sport_types', 'required|array', function ($input) {
            return $input->role_id == 3;
        });

        $validator->sometimes('description', 'required|string|max:5000', function ($input) {
            return $input->role_id == 3;
        });
    }
}
