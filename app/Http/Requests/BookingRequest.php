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
            'full_name' => 'required|string:max:500',
            'phone_number' => 'required|regex:/^\+?[0-9]{10,}$/',
            'price' => 'required|integer',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'source' => 'required|string|in:manual',
        ];
    }
    public function attributes(): array
    {
        return [
            'court_id' => 'Корт',
            'user_id' => 'Пользователь',
            'full_name' => 'Полное имя',
            'phone_number' => 'Номер телефона',
            'price' => 'Цена',
            'date' => 'Дата',
            'start_time' => 'Время начала',
            'end_time' => 'Время окончания',
            'source' => 'Источник',
        ];
    }

}
