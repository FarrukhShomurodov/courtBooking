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
            'password' => 'nullable|string',
        ];
    }
}
