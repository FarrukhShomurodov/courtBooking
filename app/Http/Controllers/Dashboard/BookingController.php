<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(): View
    {
        $role = Auth::user()->roles()->first()->name;

        if ( $role !== 'admin' && $role !== 'owner stadium') {
            $bookings = Auth::user()->bookings()->get();
        } else {
            $bookings = Booking::with(['court', 'user', 'day', 'hour'])->get();
        }
        return view('booking.index', compact('bookings'));
    }

    public function create(): View
    {
        $courts = Court::query()->where('is_active', true)->get();
        $users = User::all();
        return view('booking.create', compact('courts', 'users'));
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $this->bookingService->store($request->validated());
        return redirect()->route('bookings.index');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $this->bookingService->destroy($booking);
        return redirect()->route('bookings.index');
    }
}
