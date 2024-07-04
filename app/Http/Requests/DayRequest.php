<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DayRequest extends FormRequest
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
            'date' => 'required|date',
            'hours' => 'required|array',
            'hours.*.start_time' => 'required_without:hours.*.delete',
            'hours.*.end_time' => 'required_without:hours.*.delete|after:hours.*.start_time',
            'hours.*.delete' => 'required|in:0,1'
        ];
    }
}
