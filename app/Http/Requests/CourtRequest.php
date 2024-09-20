<?php

namespace App\Http\Requests;

use App\Rules\IsActiveStadium;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CourtRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $userStadium = Auth::user()->stadiumOwner()->first();
        if (isset($userStadium)) {
            $this->merge([
                'stadium_id' => $userStadium->id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
//            'description' => 'required|string|max:400',
//            'photos' => 'sometimes|array|max:25',
//            'photos.*' => 'image|mimes:jpg,png',
            'is_active' => 'nullable|boolean',
            'stadium_id' => [
                'required',
                'exists:stadiums,id',
                new IsActiveStadium($this->stadium_id),
            ],
            'sport_type_id' => 'required|exists:sport_types,id',
            'schedule' => 'required|array',
            'schedule.*' => 'required|array',
            'schedule.*.cost' => 'required|numeric',
            'schedule.*.start_time' => 'required|date_format:H:i',
            'schedule.*.end_time' => 'required|date_format:H:i|after:hours.*.start_time',
        ];
    }
}
