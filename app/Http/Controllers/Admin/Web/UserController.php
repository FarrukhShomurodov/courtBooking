<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\SportType;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(): View
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::all();
        $sportTypes = SportType::all();
        return view('admin.users.create', compact('roles', 'sportTypes'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->userService->store($request->validated());
        return redirect()->route('users.index');
    }

    public function edit(User $user): View
    {
        $roles = Role::all();
        $sportTypes = SportType::all();
        return view('admin.users.edit', compact('user', 'roles', 'sportTypes'));
    }

    public function update(UpdateRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());
        return redirect()->route('users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->userService->destroy($user);
        return redirect()->route('users.index');
    }
}
