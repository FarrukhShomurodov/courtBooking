<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\View\View;

class BotUserController extends Controller
{
    public function index(): View
    {
        $botUsers = BotUser::all();
        return view('users.bot-users', compact('botUsers'));
    }
}
