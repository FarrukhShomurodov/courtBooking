<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Telegram\BookingRequest;
use App\Http\Requests\Telegram\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use App\Traits\ScheduleHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function show(BookingItem $booking): JsonResponse
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

        $slots = $validated['slots'];

        $totalSum = 0;

        $bookingId = DB::transaction(function () use ($slots, $validated, &$totalSum) {
            $booking = Booking::create([
                'bot_user_id' => $validated['bot_user_id'],
            ]);

            foreach ($slots as $slot) {
                $isAvailable = $this->checkCourtAvailability($slot['court_id'], $slot['start'], $slot['end'], $slot['date']);

//                $isAvailable = BookingItem::query()
//                    ->where('court_id', $slot['court_id'])
//                    ->where('status', 'paid')
//                    ->where('start_time', $slot['start'].':00')
//                    ->where('end_time', $slot['end'].':00')
//                    ->where('date', $slot['date'])
//                    ->exists();

                if ($isAvailable) {
                    throw new \Exception('В указанное время корт недоступен.');
                }

                $bookItem = $booking->bookingItems()->create([
                    'court_id' => $slot['court_id'],
                    'full_name' => $validated['full_name'],
                    'phone_number' => $validated['phone_number'],
                    'price' => $slot['price'] * 1000,
                    'date' => $slot['date'],
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'source' => $validated['source'],
                ]);

                $totalSum += $bookItem->price;
            }

            return $booking->id;
        });

        return response()->json([
            'message' => 'Booking successful',
            'total_sum' => $totalSum,
            'booking_id' => $bookingId
        ], 200);
    }


    public function update(BookingItem $booking, UpdateBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (count($validated['slots']) > 1) {
            return response()->json(['message' => 'Пожалуйста, выберите только один слот.'], 422);
        }

        foreach ($validated['slots'] as $slot) {

            if (round($booking->price) === $slot['price'] * 1000) return response()->json(['message' => 'Пожалуйста, выберите другое время, так как указанная цена не соответствует вашему выбору.'], 422);
            if ($booking->is_edit) return response()->json(['message' => 'Вы не можете изменить бронь более одного раза.'], 422);

            $booking->update([
                'date' => $slot['date'],
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'is_edit' => true,
            ]);
        }

        return response()->json(['message' => 'Бронирование успешно обновлено.'], 200);
    }

}
