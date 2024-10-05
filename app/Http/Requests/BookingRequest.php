<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BookingRequest extends FormRequest
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
        if ($this->user_id == null) {
            $this->merge([
                'user_id' => Auth::user()->id
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
            'court_id' => 'required|exists:courts,id',
            'user_id' => 'required|exists:users,id',
            'full_name' => 'required|string|max:500',
            'phone_number' => 'required|regex:/^\+?[0-9]{10,}$/',
            'price' => 'required|integer',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => [
                'required',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) {
                    $startTime = $this->input('start_time');
                    if ($value === '00:00:00') {
                        $value = '24:00:00';
                    }

                    if (strtotime($startTime) >= strtotime($value)) {
                        $fail(trans('validation.time_after'));
                    }
                }
            ],
            'source' => 'required|string|in:manual',
        ];
    }
}
