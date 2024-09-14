<?php

namespace App\Http\Requests;

use App\Rules\UniqueManager;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueOwner;
use App\Rules\UniqueCoach;
use App\Rules\CoachHasSportType;

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
        $stadiumId = isset($this->route()->parameters['stadium']) ? $this->route()->parameters['stadium']->id : null;
        $sportTypeIds = $this->input('sport_types');

        return [
            'name' => 'required|string|max:200',
            'description' => 'required|string|max:400',
            'address' => 'required|string',
            'map_link' => 'required|string',
            'sport_types' => 'required|array',
            'sport_types.*' => 'required|integer|exists:sport_types,id',
            'photos' => 'sometimes|array',
            'photos.*' => 'image|mimes:jpg,png',
            'is_active' => 'nullable|boolean',
            'owner_id' => [
                'required',
                'exists:users,id',
                new UniqueOwner($stadiumId)
            ],
            'manager_id' => [
                'required',
                'exists:users,id',
                new UniqueManager($stadiumId)
            ],
            'coach_id' => [
                'nullable',
                'exists:users,id',
                new UniqueCoach($stadiumId),
                new CoachHasSportType($sportTypeIds),
            ],
        ];
    }
}
