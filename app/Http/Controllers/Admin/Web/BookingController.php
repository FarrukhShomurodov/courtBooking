<?php

namespace App\Http\Controllers\Admin\Web;

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
        return view('admin.booking.index', compact('courts', 'users'));
    }

    public function fetchAllBooking(): View
    {
        $role = Auth::user()->roles()->first()->name;

        switch ($role) {
            case 'admin':
                $bookings =  Booking::query()->get()->load('court');
                break;
            case 'owner stadium':
                $owner = Auth::user()->stadiumOwner()->first();
                if ($owner) {
                    $bookings = Booking::whereIn('court_id', $owner->courts->pluck('id'))->get()->load('court');
                } else {
                    $bookings = collect();
                }
                break;
                break;
        }

        return view('admin.booking.all', compact('bookings'));
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $this->bookingService->store($request->validated());
        return redirect()->route('bookings.index');
    }
}
