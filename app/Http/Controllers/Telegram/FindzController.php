<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BotUser;
use App\Models\Coach;
use App\Models\Court;
use App\Models\SportType;
use App\Models\Stadium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class FindzController extends Controller
{
    //Main pages
    public function courts(Request $request): View
    {
        if (is_null(SportType::first())){
            abort(500);
        }
        $locale = Session::get('locale', config('app.locale'));

        App::setLocale($locale);

        $sportTypeId = $request->input('sportTypeId') ?? SportType::first()->id;
        $sportTypes = SportType::all();

        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // Получаем стадионы по выбранному типу спорта
        $stadiums = SportType::findOrFail($sportTypeId)
            ->stadiums()
            ->where('is_active', true)
            ->whereHas('courts', function ($query) use ($sportTypeId) {
                $query->where('is_active', true)
                    ->where('sport_type_id', $sportTypeId);
            })
            ->get();



        // Проверяем наличие свободных кортов и фильтруем стадионы
        $stadiums->each(function ($stadium) use ($date, $startTime, $endTime) {
            $stadium->filteredCourts = $stadium->filterCourts($date, $startTime, $endTime);
        });

        // Фильтруем стадионы без свободных кортов
        $stadiums = $stadiums->filter(function ($stadium) {
            return $stadium->filteredCourts->isNotEmpty();
        });

        if ($stadiums->isEmpty()) {
            $stadiums = collect();
        }

        return view('findz.pages.stadiums', [
            'sportTypes' => $sportTypes,
            'stadiums' => $stadiums,
            'currentSportTypeId' => $sportTypeId
        ]);
    }

    public function courtsBySportType(SportType $sportType, Request $request): View
    {
        if (is_null(SportType::first())){
            abort(500);
        }

        $sportTypes = SportType::all();

        $sportTypeId = $sportType->id ?? SportType::first()->id;

        $date = $request->input('date');

        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        // Получаем стадионы по выбранному типу спорта
        $stadiums = SportType::findOrFail($sportTypeId)
            ->stadiums()
            ->where('is_active', true)
            ->whereHas('courts', function ($query) use ($sportTypeId) {
                $query->where('is_active', true)
                    ->where('sport_type_id', $sportTypeId);
            })
            ->get();

        // Проверяем наличие свободных кортов и фильтруем стадионы
        $stadiums->each(function ($stadium) use ($date, $startTime, $endTime) {
            $stadium->filteredCourts = $stadium->filterCourts($date, $startTime, $endTime);
        });

        // Фильтруем стадионы без свободных кортов
        $stadiums = $stadiums->filter(function ($stadium) {
            return $stadium->filteredCourts->isNotEmpty();
        });

        if ($stadiums->isEmpty()) {
            $stadiums = collect();
        }

        return view('findz.pages.stadiums', [
            'sportTypes' => $sportTypes,
            'stadiums' => $stadiums,
            'currentSportTypeId' => $sportTypeId
        ]);
    }


    public function coachesBySportType(SportType $sportType, Request $request): View
    {
        if (is_null(SportType::first())){
            abort(500);
        }

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

    public function stadiumShow(Stadium $stadium, Request $request): View
    {
        $currentSportTypeId = $request->get('sportType');

        return view('findz.pages.show.stadium', compact('stadium', 'currentSportTypeId'));
    }


    public function book(Request $request): View
    {
        $currentSportTypeId = $request->input('sportType');
        $stadium = Stadium::query()->find($request->get('stadium'));
        $courts = $stadium->courts()->where('is_active', true)->where('sport_type_id', $currentSportTypeId)->get();

        $isUpdate = false;

        return view('findz.book', compact('courts', 'stadium', 'currentSportTypeId', 'isUpdate'));
    }

    public function bookUpdate(BookingItem $booking, Request $request): View
    {
        $stadium = $booking->court()->first()->stadium;
        $currentSportTypeId = $booking->court->sportTypes->id;
        $courts = $stadium->courts()->with('schedules')->where('is_active', true)->where('sport_type_id', $currentSportTypeId)->get();

        $isUpdate = true;
        $userBook = $booking;
        return view('findz.book', compact('courts', 'userBook', 'currentSportTypeId', 'isUpdate', 'stadium'));
    }

    public function myBookings(Request $request)
    {
        $stadiums = Stadium::all();
        $currentSportTypeId = $request->input('sportType');
        $botUserId = $request->input('bot_user_id');

        if ($botUserId) {
            $botUser = BotUser::query()->where('chat_id', $botUserId)->first();
            $bookings = Booking::where('bot_user_id', $botUser->id)
                ->whereHas('bookingItems', function ($query) {
                    $query->where('source', 'bot')->where('status', 'paid');
                })
                ->get();

            return view('findz.pages.mybookings', compact('currentSportTypeId', 'stadiums', 'bookings'));
        } else {
            return redirect()->route('webapp');
        }
    }

}
