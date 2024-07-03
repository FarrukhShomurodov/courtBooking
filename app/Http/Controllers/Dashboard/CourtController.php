<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourtRequest;
use App\Models\Court;
use App\Models\Stadium;
use App\Services\CourtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourtController extends Controller
{
    protected CourtService $courtService;

    public function __construct(CourtService $courtService)
    {
        $this->courtService = $courtService;
    }

    public function index(): View
    {
        $courts = Court::query()->with('stadium')->get();
        return view('court.index', compact('courts'));
    }

    public function create(): View
    {
        $stadiums = Stadium::query()->where('is_active', true)->get();
        return view('court.create', compact('stadiums'));

    }

    public function store(CourtRequest $request): RedirectResponse
    {
        $this->courtService->store($request->validated());
        return redirect()->route('courts.index');
    }

    public function edit(Court $court): View
    {
        $stadiums = Stadium::query()->where('is_active', true)->get();

        return view('court.edit', compact('court', 'stadiums'));
    }

    public function update(CourtRequest $request, Court $court): RedirectResponse
    {
        $this->courtService->update($court, $request->validated());
        return redirect()->route('courts.index');
    }

    public function destroy(Court $court): RedirectResponse
    {
        $this->courtService->destroy($court);
        return redirect()->route('courts.index');
    }
}
