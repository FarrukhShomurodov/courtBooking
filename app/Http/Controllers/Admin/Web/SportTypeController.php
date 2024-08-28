<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SportTypeRequest;
use App\Models\SportType;
use App\Services\SportTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SportTypeController extends Controller
{
    protected SportTypeService $sportTypeService;

    public function __construct(SportTypeService $sportTypeService)
    {
        $this->sportTypeService = $sportTypeService;
    }

    public function index(): View
    {
        $spotTypes = SportType::all();
        return view('admin.sport_types.index', compact('spotTypes'));
    }

    public function create(): View
    {
        return view('admin.sport_types.create');

    }

    public function store(SportTypeRequest $request): RedirectResponse
    {
        $this->sportTypeService->store($request->validated());
        return redirect()->route('sport-types.index');
    }

    public function edit(SportType $sportType): View
    {
        return view('admin.sport_types.edit', compact('sportType'));
    }

    public function update(SportTypeRequest $request, SportType $sportType): RedirectResponse
    {
        $this->sportTypeService->update($sportType, $request->validated());
        return redirect()->route('sport-types.index');
    }

    public function destroy(SportType $sportType): RedirectResponse
    {
        $this->sportTypeService->destroy($sportType);
        return redirect()->route('sport-types.index');
    }
}
