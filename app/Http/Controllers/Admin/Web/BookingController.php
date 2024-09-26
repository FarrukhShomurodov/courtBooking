<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
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
            $courts = Auth::user()->stadiumOwner->courts()->where('is_active', true)->get()->load('schedules');
        } elseif (Auth::user()->roles()->first()->name == 'stadium manager') {
            if (Auth::user()->stadiumManager) {
                if (Auth::user()->stadiumManager->is_active == 1) {
                    $courts = Auth::user()->stadiumManager->courts()->where('is_active', true)->get()->load('schedules');
                } else {
                    Auth::logout();
                    return redirect()->route('login')->withErrors(['error' => 'Стадион к которому вы преклеплены не активен.']);
                }
            } else {
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Вы не прикреплены ни к одному стадиону.']);
            }
        } elseif (Auth::user()->roles()->first()->name == 'trainer') {
            if (Auth::user()->coach()->count() > 0) {
                if (Auth::user()->coach->stadium->is_active == 1) {
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
                $owner = Auth::user()->stadiumOwner;
                if ($owner) {
                    $bookings = BookingItem::whereIn('court_id', $owner->courts->pluck('id'))->get()->load('court');
                } else {
                    $bookings = collect();
                }
                break;
            case 'stadium manager':
                $stadiumManager = Auth::user()->stadiumManager;
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
                    $owner = Auth::user()->stadiumOwner;
                    if ($owner) {
                        $hasBooking = BookingItem::query()
                            ->whereIn('court_id', $owner->courts->pluck('id'))
                            ->where('id', $booking->id)
                            ->exists();

                        if ($hasBooking) {
                            $this->bookingService->delete($booking);
                        } else {
                            return redirect()->route('all-bookings')->withErrors('Бронирование на этот стадион не найдено.');
                        }
                    } else {
                        return redirect()->route('all-bookings')->withErrors('Unauthorized to delete booking.');
                    }
                    break;

                case 'stadium manager':
                    $stadiumManager = Auth::user()->stadiumManager;
                    if ($stadiumManager) {
                        $hasBooking = BookingItem::query()
                            ->whereIn('court_id', $stadiumManager->courts->pluck('id'))
                            ->where('id', $booking->id)
                            ->exists();

                        if ($hasBooking) {
                            $this->bookingService->delete($booking);
                        } else {
                            return redirect()->route('all-bookings')->withErrors('Бронирование у этого менеджера не найдено.');
                        }
                    } else {
                        return redirect()->route('all-bookings')->withErrors('Unauthorized to delete booking.');
                    }
                    break;

                case 'trainer':
                    $trainer = Auth::user()->coach;

                    if ($trainer) {
                        $hasBooking = BookingItem::query()
                            ->whereIn('court_id', $trainer->stadium->courts->pluck('id'))
                            ->where('id', $booking->id)
                            ->exists();
                        if ($hasBooking) {
                            $this->bookingService->delete($booking);
                        } else {
                            return redirect()->route('all-bookings')->withErrors('Бронирование на этого тренера не найдено.');
                        }
                    } else {
                        return redirect()->route('all-bookings')->withErrors('Unauthorized to delete booking.');
                    }
                    break;

                default:
                    return redirect()->route('all-bookings')->withErrors('Invalid role.');
            }

            return redirect()->route('all-bookings')->with('success', 'Бронирование успешно удалено.');
        } else {
            return redirect()->route('all-bookings')->withErrors('Вы не можете удалить бронирование пользователя бота.');
        }

    }
}
