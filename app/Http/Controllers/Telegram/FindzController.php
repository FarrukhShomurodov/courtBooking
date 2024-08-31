<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BotUser;
use App\Models\Coach;
use App\Models\Court;
use App\Models\SportType;
use App\Models\Stadium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FindzController extends Controller
{
    //Main pages
    public function courts(Request $request): View
    {
        $language = $request->get('lang', 'ru');

        App::setLocale($language);
        session(['locale' => $language]);

        $sportTypeId = $request->input('sportTypeId') ?? SportType::first()->id;
        $sportTypes = SportType::all();

        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        if ($date || ($startTime && $endTime)) {
            // Если есть дата или оба времени
            $courts = $sportTypes->first()->courts()
                ->where('is_active', true)
                ->with('sportTypes')
                ->with('stadium')
                ->with(['bookings' => function ($query) use ($date, $startTime, $endTime) {
                    if ($date) {
                        $query->where('date', $date);
                        $query->where('status', 'paid');
                    }

                    if ($startTime && $endTime) {
                        $query->where(function ($query) use ($startTime, $endTime) {
                            $query->whereBetween('start_time', [$startTime, $endTime])
                                ->orWhereBetween('end_time', [$startTime, $endTime])
                                ->orWhere(function ($query) use ($startTime, $endTime) {
                                    $query->where('start_time', '<', $startTime)
                                        ->where('end_time', '>', $endTime);
                                });
                        });
                    }
                }])
                ->get();

            $courts = $courts->filter(function ($court) {
                return $court->bookings->isEmpty();
            });
        } else {
            // Без фильтрации по бронированиям
            $courts = $sportTypes->first()->courts()
                ->where('is_active', true)
                ->with('sportTypes')
                ->with('stadium')
                ->with('bookings')
                ->get();
        }

        return view('findz.pages.courts', [
            'sportTypes' => $sportTypes,
            'courts' => $courts,
            'currentSportTypeId' => $sportTypeId
        ]);
    }

    public function courtsBySportType(SportType $sportType, Request $request): View
    {
        $sportTypes = SportType::all();

        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        if ($date || ($startTime && $endTime)) {
            // Если есть дата или оба времени
            $courts = $sportType->courts()
                ->where('is_active', true)
                ->with('sportTypes')
                ->with('stadium')
                ->with(['bookings' => function ($query) use ($date, $startTime, $endTime) {
                    if ($date) {
                        $query->where('date', $date);
                        $query->where('status', 'paid');
                    }

                    if ($startTime && $endTime) {
                        $query->where(function ($query) use ($startTime, $endTime) {
                            $query->whereBetween('start_time', [$startTime, $endTime])
                                ->orWhereBetween('end_time', [$startTime, $endTime])
                                ->orWhere(function ($query) use ($startTime, $endTime) {
                                    $query->where('start_time', '<', $startTime)
                                        ->where('end_time', '>', $endTime);
                                });
                        });
                    }
                }])
                ->get();

            $courts = $courts->filter(function ($court) {
                return $court->bookings->isEmpty();
            });
        } else {
            // Без фильтрации по бронированиям
            $courts = $sportType->courts()
                ->where('is_active', true)
                ->with('sportTypes')
                ->with('stadium')
                ->with('bookings')
                ->get();
        }

        return view('findz.pages.courts', [
            'sportTypes' => $sportTypes,
            'courts' => $courts,
            'currentSportTypeId' => $sportType->id
        ]);
    }


    public function coachesBySportType(SportType $sportType, Request $request): View
    {
        $sportTypes = SportType::whereHas('coaches')->get();

        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        if ($date || ($startTime && $endTime)) {
            $coaches = $sportType->coaches()
                ->whereHas('stadium')
                ->with(['stadium' => function ($query) use ($date, $startTime, $endTime) {
                    $query->with(['courts' => function ($query) use ($date, $startTime, $endTime) {
                        if ($date) {
                            $query->whereHas('bookings', function ($query) use ($date) {
                                $query->where('date', $date);
                                $query->where('status', 'paid');

                            });
                        }

                        if ($startTime && $endTime) {
                            $query->whereDoesntHave('bookings', function ($query) use ($startTime, $endTime) {
                                $query->where(function ($query) use ($startTime, $endTime) {
                                    $query->whereBetween('start_time', [$startTime, $endTime])
                                        ->orWhereBetween('end_time', [$startTime, $endTime])
                                        ->orWhere(function ($query) use ($startTime, $endTime) {
                                            $query->where('start_time', '<', $startTime)
                                                ->where('end_time', '>', $endTime);
                                        });
                                });
                            });
                        }
                    }]);
                }])
                ->get();

            // Фильтрация стадионов и тренеров
            foreach ($coaches as $coach) {
                $coach->stadium->courts = $coach->stadium->courts->filter(function ($court) {
                    return $court->bookings->isEmpty();
                });
            }

            $coaches = $coaches->filter(function ($coach) {
                return $coach->stadium->courts->isNotEmpty();
            });
        } else {
            $coaches = $sportType->coaches()
                ->whereHas('stadium')
                ->get();
        }

        return view('findz.pages.coach', [
            'sportTypes' => $sportTypes,
            'coaches' => $coaches,
            'currentSportTypeId' => $sportType->id
        ]);
    }


    public function filter(SportType $sportType): View
    {
        return view('findz.filter', [
            'currentSportTypeId' => $sportType->id
        ]);
    }

    // Show pages
    public function coachShow(Coach $coach): View
    {
        $coach->load('sportTypes')->load('user')->load('stadium');
        $courts = $coach->stadium()->first()->courts;
        $minPrice = 0;

        foreach ($courts as $court) {
            $minPrice += $court->getMinimumCost();
        }
        $currentSportTypeId = false;

        return view('findz.pages.show.coach', compact('coach', 'minPrice', 'currentSportTypeId'));
    }

    public function courtShow(Court $court): View
    {
        $currentSportTypeId = false;

        return view('findz.pages.show.court', compact('court', 'currentSportTypeId'));
    }


    public function book(Request $request): View
    {
        $courts = Court::with('schedules')->where('is_active', true)->get();
        $currentSportTypeId = $request->input('sportType');
        $isUpdate = false;
        return view('findz.book', compact('courts', 'currentSportTypeId', 'isUpdate'));
    }

    public function bookUpdate(Booking $booking, Request $request): View
    {
        $courts = Court::with('schedules')->where('is_active', true)->get();
        $currentSportTypeId = $request->input('sportType');
        $isUpdate = true;
        $userBook = $booking;
        return view('findz.book', compact('courts', 'userBook', 'currentSportTypeId', 'isUpdate'));
    }

    public function myBookings(Request $request): View
    {
        $stadiums = Stadium::all();
        $currentSportTypeId = $request->input('sportType');
        $botUserId = $request->input('bot_user_id');
        dd($botUserId);
        $botUser = BotUser::query()->where('chat_id', $botUserId)->first();

        $bookings = Booking::where('bot_user_id', $botUser->id)->where('source', 'bot')->where('status', 'paid')->get();

        return view('findz.pages.mybookings', compact('currentSportTypeId', 'stadiums', 'bookings'));
    }

}
