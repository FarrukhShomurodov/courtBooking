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

        $owners = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'owner stadium');
        })->get();

        $managers = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'stadium manager');
        })->get();

        $coaches = Coach::query()->whereDoesntHave('stadium')->get();
        return view('admin.stadium.create', compact('sportTypes', 'owners', 'managers', 'coaches'));

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

        $owners = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'owner stadium');
        })->get();

        $managers = User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'stadium manager');
        })->get();

        $coaches = Coach::query()->whereDoesntHave('stadium');
        return view('admin.stadium.edit', compact('stadium', 'sportTypes', 'owners', 'managers', 'coaches'));
    }

    public function update(StadiumRequest $request, Stadium $stadium): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['is_active'] == 0 && $this->stadiumHasBookings($stadium)) {
            return redirect()->route('stadiums.create')->withErrors(__('errors.cannot_inactive_stadium_due_to_has_book'));
        }

        $this->stadiumService->update($stadium, $validated);
        return redirect()->route('stadiums.index');
    }

    public function destroy(Stadium $stadium): RedirectResponse
    {
        if ($this->stadiumHasBookings($stadium)) {
            return redirect()->route('stadiums.index')->withErrors(__('errors.cannot_delete_stadium_due_to_has_book'));
        }
        $this->stadiumService->destroy($stadium);
        return redirect()->route('stadiums.index');
    }
}
