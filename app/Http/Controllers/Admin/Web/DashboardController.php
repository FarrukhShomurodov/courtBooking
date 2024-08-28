<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Repositories\StatisticsRepositories;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected StatisticsRepositories $statisticsRepository;

    public function __construct(StatisticsRepositories $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function index(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|Factory|Application
    {
        if (Auth::user()->roles()->first()->name == 'admin') {
            $statistics = $this->statisticsRepository->adminStatistics();
        } elseif (Auth::user()->roles()->first()->name == 'owner stadium') {
            $statistics = $this->statisticsRepository->stadiumOwnerStatistics();
        } else {
            $statistics = [];
        }

        return view('admin.dashboard', compact('statistics'));
    }

    public function setLocale($locale, $botUser = null): RedirectResponse
    {
        if (!in_array($locale, ['ru', 'uz'])) {
            abort(400, 'Unsupported locale');
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        if ($botUser) {
            $user = BotUser::find($botUser);

            if ($user) {
                $user->update(['lang' => $locale]);
            } else {
                abort(404, 'Bot user not found');
            }
        }

        return redirect()->back();
    }
}
