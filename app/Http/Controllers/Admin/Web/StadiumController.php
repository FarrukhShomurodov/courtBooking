<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StadiumRequest;
use App\Models\Coach;
use App\Models\SportType;
use App\Models\Stadium;
use App\Models\User;
use App\Services\StadiumService;
use App\Traits\BookingTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class StadiumController extends Controller
{
    use BookingTrait;

    protected StadiumService $stadiumService;

    public function __construct(StadiumService $stadiumService)
    {
        $this->stadiumService = $stadiumService;
    }

    public function index(): View
    {
        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $stadiums = Auth::user()->stadiumOwner()->with('coach')->with('owner')->with('sportTypes')->get();
        } else {
            $stadiums = Stadium::query()->with('coach')->with('owner')->with('sportTypes')->get();
        }
        return view('admin.stadium.index', compact('stadiums'));
    }

    public function create(): View
    {
        $sportTypes = SportType::all();
        $users = User::query()->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin')
            ->orWhere('name', 'trainer');
        })->get();
        $coaches = Coach::query()->whereDoesntHave('stadium')->get();
        return view('admin.stadium.create', compact('sportTypes', 'users', 'coaches'));

    }

    public function store(StadiumRequest $request): RedirectResponse
    {
        $this->stadiumService->store($request->validated());
        return redirect()->route('stadiums.index');
    }

    public function edit(Stadium $stadium): View
    {
        $stadium->load("coach");
        $sportTypes = SportType::all();
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
        $coaches = Coach::query()->whereDoesntHave('stadium');
        return view('admin.stadium.edit', compact('stadium', 'sportTypes', 'users','coaches'));
    }

    public function update(StadiumRequest $request, Stadium $stadium): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['is_active'] == 0 && $this->stadiumHasBookings($stadium)) {
            return redirect()->route('stadiums.create')->withErrors('Невозможно деактивировать стадион, так как у кортов есть активные бронирования.');
        }

        $this->stadiumService->update($stadium, $validated);
        return redirect()->route('stadiums.index');
    }

    public function destroy(Stadium $stadium): RedirectResponse
    {
        if ($this->stadiumHasBookings($stadium)) {
            return redirect()->route('stadiums.index')->withErrors('Невозможно удалить стадион, так как у кортов есть активные бронирования.');
        }
        $this->stadiumService->destroy($stadium);
        return redirect()->route('stadiums.index');
    }
}