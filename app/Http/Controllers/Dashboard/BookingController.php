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
        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $courts = Auth::user()->stadiumOwner()->first()->courts()->where('is_active', true)->get()->load('schedules');
        } else {
            $courts = Court::query()->where('is_active', true)->get()->load('schedules');
        }
        $users = User::all();
        return view('booking.index', compact('courts', 'users'));
    }

    public function fetchAllBooking():  View
    {
        $bookings = Booking::query()->get()->load('court');
        return view('booking.all', compact('bookings'));
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $this->bookingService->store($request->validated());
        return redirect()->route('bookings.index');
    }
}
