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
        // Получаем текущее бронирование
        $bookingId = $this->route('booking')->id ?? null;
        $booking = Booking::query()->find($bookingId);

        return [
            'user_id' => 'required|exists:bot_users,id',
            'full_name' => 'required|string:max:500',
            'phone_number' => 'required|regex:/^\+?[0-9]{10,}$/',
            'date' => 'required|date|after_or_equal:today',
            'slots' => 'required|array',
            'slots.*.court_id' => 'required|exists:courts,id',
            'slots.*.start_time' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) use ($booking) {
                $bookingStartTime = Carbon::parse($booking->start_time)->format('H:i');
                if ($booking && $value !== $bookingStartTime) {
                    $fail('Нельзя изменить время начала бронирования.');
                }
            }],
            'slots.*.end_time' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) use ($booking) {
                $bookingEndTime = Carbon::parse($booking->end_time)->format('H:i');
                if ($booking && $value !== $bookingEndTime) {
                    $fail('Нельзя изменить время окончания бронирования.');
                }
            }],
            'slots.*.price' => 'required|integer',
            'source' => 'required|string|in:bot',
        ];
    }
}
