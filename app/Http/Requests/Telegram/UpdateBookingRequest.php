<?php

namespace App\Http\Requests\Telegram;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
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
            'bot_user_id' => 'required|exists:bot_users,id',
            'full_name' => 'required|string:max:500',
            'phone_number' => 'required|regex:/^\+?[0-9]{10,}$/',
            'slots' => 'required|array',
            'slots.*.court_id' => 'required|exists:courts,id',
            'slots.*.start' => 'required|date_format:H:i',
            'slots.*.end' => 'required|date_format:H:i|after:start_time',
            'slots.*.date' => 'required|date|after_or_equal:today',
            'slots.*.price' => 'required|integer',
        ];
    }

    public function attributes(): array
    {
        return [
            'bot_user_id' => 'Пользователь бота',
            'full_name' => 'Полное имя',
            'phone_number' => 'Номер телефона',
            'slots' => 'Слоты',
            'slots.*.court_id' => 'Площадка',
            'slots.*.start' => 'Время начала',
            'slots.*.end' => 'Время окончания',
            'slots.*.date' => 'Дата',
            'slots.*.price' => 'Цена',
        ];
    }
}
