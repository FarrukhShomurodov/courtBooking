<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\View\View;

class BotUserController extends Controller
{
    public function index(): View
    {
        $botUsers = BotUser::all();
        return view('admin.users.bot-users', compact('botUsers'));
    }
}
