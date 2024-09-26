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

        return back()->withErrors(['login' => 'Неверные данные для входа в систему']);
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
            if (Auth::user()->stadiumOwner->count() > 0) {
                if (Auth::user()->stadiumOwner->is_active == 1) {
                    return redirect()->route('dashboard');
                } else {
                    Auth::logout();
                    return redirect()->route('login')->withErrors(['error' => 'Стадион к которому вы преклеплены не активен.']);
                }
            } else {
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Вы не прикреплены ни к одному стадиону.']);
            }
        }

        return redirect('/bookings');
    }
}
