<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DayRequest;
use App\Http\Requests\ScheduleRequest;
use App\Models\Court;
use App\Models\Day;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(): View
    {
        if (Auth::user()->roles()->first()->name !== 'admin') {
            $courts = Auth::user()->stadiumOwner()->first()->courts()->get();
        } else {
            $courts = Court::all();
        }
        return view('schedule.index', compact('courts'));
    }

    public function create(): View
    {
        if (Auth::user()->roles()->first()->name !== 'admin') {
            $courts = Auth::user()->stadiumOwner()->first()->courts()->where('is_active', true)->get();
        } else {
            $courts = Court::query()->where('is_active', true)->get();
        }
        return view('schedule.create', compact('courts'));
    }

    public function store(ScheduleRequest $request): RedirectResponse
    {
        $this->scheduleService->store($request->validated());

        return redirect()->route('schedule.index');
    }

    public function edit(Day $day): View
    {
        if (Auth::user()->roles()->first()->name !== 'admin') {
            $courts = Auth::user()->stadiumOwner()->first()->courts()->where('is_active', true)->get();
        } else {
            $courts = Court::query()->where('is_active', true)->get();
        }
        return view('schedule.edit', compact('day', 'courts'));
    }

    public function update(DayRequest $request, Day $day): RedirectResponse
    {
        $this->scheduleService->update($day, $request->validated());

        return redirect()->route('schedule.index');
    }

    public function destroy(Day $day): RedirectResponse
    {
        $dataNow = date('Y-m-d');
        if ($day->date >= $dataNow) {
            return redirect()->route('schedule.index')->withErrors('Невозможно удалить будущий или текущий день.');
        } else {
            $this->scheduleService->destroy($day);
            return redirect()->route('schedule.index');
        }
    }
}
