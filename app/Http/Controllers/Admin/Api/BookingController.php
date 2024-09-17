<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Telegram\BookingRequest;
use App\Http\Requests\Telegram\UpdateBookingRequest;
use App\Models\Booking;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use App\Traits\ScheduleHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ScheduleHelper;

    protected BookingRepository $bookingRepository;
    protected BookingService $bookingService;

    public function __construct(BookingRepository $bookingRepository, BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
        $this->bookingRepository = $bookingRepository;
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking);
    }

    public function showByDate(Request $request): JsonResponse
    {
        $validated = $request->validate(['date' => 'required|date']);

        $booking = $this->bookingRepository->byDate($validated['date']);

        return response()->json($booking);
    }

    public function store(BookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (count($validated['slots']) > 1) {
            return response()->json(['message' => 'Пожалуйста, выберите только один слот.'], 422);
        }

        $date = $validated['date'];
        $fullName = $validated['full_name'];
        $phoneNumber = $validated['phone_number'];
        $slots = $validated['slots'];


        $totalSum = 0;
        $bookingIds = [];
        foreach ($slots as $slot) {
            $isAvailable = $this->checkCourtAvailability($slot['court_id'], $slot['start_time'], $slot['end_time'], $date);

            if ($isAvailable) {
                return response()->json(['message' => 'В указанное время корт недоступен.'], 422);
            }

            $booking = Booking::create([
                'court_id' => $slot['court_id'],
                'bot_user_id' => $validated['bot_user_id'],
                'full_name' => $fullName,
                'phone_number' => $phoneNumber,
                'price' => $slot['price'] * 1000,
                'date' => $date,
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'source' => $validated['source'],
            ]);
            $bookingIds[] = $booking->id;
            $totalSum += $booking->price;
        }


        return response()->json([
            'message' => 'Booking successful',
            'total_sum' => $totalSum,
            'booking_ids' => $bookingIds
        ], 200);
    }

    public function update(Booking $booking, UpdateBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (count($validated['slots']) > 1) {
            return response()->json(['message' => 'Пожалуйста, выберите только один слот.'], 422);
        }

        // Проверяем, что время не изменилось
        foreach ($validated['slots'] as $slot) {
            $booking->update([
                'date' => $validated['date'],
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'price' => $slot['price'],
                'source' => $validated['source'],
            ]);
        }

        return response()->json(['message' => 'Бронирование успешно обновлено.'], 200);
    }

}
