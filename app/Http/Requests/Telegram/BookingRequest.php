<?php

namespace App\Http\Requests\Telegram;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'user_id' => 'required|exists:bot_users,id',
            'full_name' => 'required|string:max:500',
            'phone_number' => 'required|regex:/^\+?[0-9]{10,}$/',
            'date' => 'required|date|after_or_equal:today',
            'slots' => 'required|array',
            'slots.*.court_id' => 'required|exists:courts,id',
            'slots.*.start_time' => 'required|date_format:H:i',
            'slots.*.end_time' => 'required|date_format:H:i|after:start_time',
            'slots.*.price' => 'required|integer',
            'source' => 'required|string|in:bot',
        ];
    }
}
