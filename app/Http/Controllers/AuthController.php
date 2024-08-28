<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{

    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('login', 'password'))) {
            return $this->redirectBasedOnRole();
        }

        return back()->withErrors(['login' => 'Invalid login details']);
    }

    public function OwnerConfirmation(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('login', 'password'))) {
            $user = Auth::user();

            if ($user->hasRole('owner stadium')) {
                session(['isOwner' => true]);
                return redirect()->route('dashboard');
            }

            session(['isOwner' => false]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'login' => 'Неверный логин или пароль.',
        ]);
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }

    protected function redirectBasedOnRole(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('dashboard');
        } elseif ($user->hasRole('owner stadium')) {
            return redirect()->route('dashboard');
        }

        return redirect('/bookings');
    }
}
