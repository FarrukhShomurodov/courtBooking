<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'second_name' => 'required|string|max:200',
            'login' => 'required|string|unique:users,login',
            'role_id' => 'required|exists:roles,id',
            'avatar' => 'nullable|image|mimes:jpg,png',
            'password' => 'required',
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
