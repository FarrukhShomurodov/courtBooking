<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StadiumRequest;
use App\Models\SportType;
use App\Models\Stadium;
use App\Models\User;
use App\Services\StadiumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StadiumController extends Controller
{
    protected StadiumService $stadiumService;

    public function __construct(StadiumService $stadiumService)
    {
        $this->stadiumService = $stadiumService;
    }

    public function index(): View
    {
        $stadiums = Stadium::query()->with('coach')->with('owner')->with('sportTypes')->get();
        return view('stadium.index', compact('stadiums'));
    }

    public function create(): View
    {
        $sportTypes = SportType::all();
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
        return view('stadium.create', compact('sportTypes', 'users'));

    }

    public function store(StadiumRequest $request): RedirectResponse
    {
        $this->stadiumService->store($request->validated());
        return redirect()->route('stadiums.index');
    }

    public function edit(Stadium $stadium): View
    {
        $sportTypes = SportType::all();
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
        return view('stadium.edit', compact('stadium', 'sportTypes', 'users'));
    }

    public function update(StadiumRequest $request, Stadium $stadium): RedirectResponse
    {
        $this->stadiumService->update($stadium, $request->validated());
        return redirect()->route('stadiums.index');
    }

    public function destroy(Stadium $stadium): RedirectResponse
    {
        $this->stadiumService->destroy($stadium);
        return redirect()->route('stadiums.index');
    }
}
