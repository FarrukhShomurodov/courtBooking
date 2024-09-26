<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\BookingItem;
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

    public function index()
    {
        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $courts = Auth::user()->stadiumOwner()->first()->courts()->where('is_active', true)->get()->load('schedules');
        } elseif (Auth::user()->roles()->first()->name == 'stadium manager') {
            if (Auth::user()->stadiumManager()->count() > 0) {
                $courts = Auth::user()->stadiumManager()->first()->courts()->where('is_active', true)->get()->load('schedules');
            } else {
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Вы не прикреплены ни к одному стадиону.']);
            }
        } elseif (Auth::user()->roles()->first()->name == 'trainer') {
            if (Auth::user()->coach()->count() > 0) {
                if (Auth::user()->coach->stadium->where('is_active', 1)->count() > 1) {
                    $courts = Auth::user()->coach->stadium->courts()->where('is_active', true)->get()->load('schedules');
                } else {
                    Auth::logout();
                    return redirect()->route('login')->withErrors(['error' => 'Стадион к которому вы преклеплены не активен.']);
                }
            } else {
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Вы не прикреплены ни к одному стадиону.']);
            }
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
                $bookings = BookingItem::query()->get()->load('court');
                break;
            case 'owner stadium':
                $owner = Auth::user()->stadiumOwner()->first();
                if ($owner) {
                    $bookings = BookingItem::whereIn('court_id', $owner->courts->pluck('id'))->get()->load('court');
                } else {
                    $bookings = collect();
                }
                break;
            case 'stadium manager':
                $stadiumManager = Auth::user()->stadiumManager()->first();
                if ($stadiumManager) {
                    $bookings = BookingItem::whereIn('court_id', $stadiumManager->courts->pluck('id'))->get()->load('court');
                } else {
                    $bookings = collect();
                }
                break;
            case 'trainer':
                $trainer = Auth::user()->coach;
                if ($trainer) {
                    $bookings = BookingItem::whereIn('court_id', $trainer->stadium->courts->pluck('id'))->get()->load('court');
                } else {
                    $bookings = collect();
                }
                break;
        }

        return view('admin.booking.all', compact('bookings'));
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $this->bookingService->store($request->validated());
        return redirect()->route('bookings.index');
    }

    public function destroy(BookingItem $booking): RedirectResponse
    {
        $role = Auth::user()->roles()->first()->name;

        if ($booking->source === 'manual') {
            switch ($role) {
                case 'admin':
                    $this->bookingService->delete($booking);
                    break;

                case 'owner stadium':
                    $owner = Auth::user()->stadiumOwner()->first();
                    if ($owner) {
                        $hasBooking = BookingItem::query()
                            ->whereIn('court_id', $owner->courts->pluck('id'))
                            ->where('id', $booking->id)
                            ->exists();

                        if ($hasBooking) {
                            $this->bookingService->delete($booking);
                        } else {
                            return redirect()->route('all-bookings')->withErrors('Booking not found for this stadium.');
                        }
                    } else {
                        return redirect()->route('all-bookings')->withErrors('Unauthorized to delete booking.');
                    }
                    break;

                case 'stadium manager':
                    $stadiumManager = Auth::user()->stadiumManager()->first();
                    if ($stadiumManager) {
                        $hasBooking = BookingItem::query()
                            ->whereIn('court_id', $stadiumManager->courts->pluck('id'))
                            ->where('id', $booking->id)
                            ->exists();

                        if ($hasBooking) {
                            $this->bookingService->delete($booking);
                        } else {
                            return redirect()->route('all-bookings')->withErrors('Booking not found for this manager.');
                        }
                    } else {
                        return redirect()->route('all-bookings')->withErrors('Unauthorized to delete booking.');
                    }
                    break;

                default:
                    return redirect()->route('all-bookings')->withErrors('Invalid role.');
            }

            return redirect()->route('all-bookings')->with('success', 'Booking deleted successfully.');
        } else {
            return redirect()->route('all-bookings')->withErrors('You cannot delete a bot user\'s booking.');
        }

    }
}
