<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourtRequest;
use App\Models\Court;
use App\Models\Stadium;
use App\Services\CourtService;
use App\Traits\BookingTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CourtController extends Controller
{
    use BookingTrait;

    protected CourtService $courtService;

    public function __construct(CourtService $courtService)
    {
        $this->courtService = $courtService;
    }

    public function index(): View
    {
        if (Auth::user()->roles()->first()->name == 'owner stadium') {
            $courts = Auth::user()->stadiumOwner->courts()->with('stadium')->get();
        } else {
            $courts = Court::query()->with('stadium')->get();
        }

        return view('admin.court.index', compact('courts'));
    }

    public function create(): View
    {
        $stadiums = Stadium::query()->where('is_active', true)->get();
        return view('admin.court.create', compact('stadiums'));
    }

    public function store(CourtRequest $request): RedirectResponse
    {
        $this->courtService->store($request->validated());
        return redirect()->route('courts.index');
    }

    public function edit(Court $court): View
    {
        $stadiums = Stadium::query()->where('is_active', true)->get();

        return view('admin.court.edit', compact('court', 'stadiums'));
    }

    public function update(CourtRequest $request, Court $court): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['is_active'] == 0 && $this->courtHasBookings($court)) {
            return redirect()->route('courts.create')->withErrors(__('validation.cannot_inactive_cort_due_to_has_book'));
        }

        $this->courtService->update($court, $validated);
        return redirect()->route('courts.index');
    }

    public function destroy(Court $court): RedirectResponse
    {
        if ($this->courtHasBookings($court)) {
            return redirect()->route('courts.index')->withErrors(__('validation.cannot_delete_cort_due_to_has_book'));
        }
        $this->courtService->destroy($court);
        return redirect()->route('courts.index');
    }
}
