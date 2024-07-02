<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NotAdmin;
use App\Rules\UniqueOwner;
use App\Rules\UniqueCoach;

class StadiumRequest extends FormRequest
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
            'description' => 'required|string',
            'address' => 'required|string',
            'map_link' => 'required|string',
            'sport_types' => 'required|array',
            'sport_types.*' => 'required|integer|exists:sport_types,id',
            'photos' => 'sometimes|array',
            'photos.*' => 'image|mimes:jpg,png',
            'is_active' => 'nullable|boolean',
            'owner_id' => ['required', 'exists:users,id', new UniqueOwner],
            'coach_id' => ['nullable', 'exists:users,id', 'different:owner_id', new NotAdmin ,new UniqueCoach],
        ];
    }
}
