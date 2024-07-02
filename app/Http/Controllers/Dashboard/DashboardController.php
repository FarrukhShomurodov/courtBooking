<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Repositories\StatisticsRepositories;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected StatisticsRepositories $userRepositories;

    public function __construct(StatisticsRepositories $userRepositories)
    {
        $this->userRepositories = $userRepositories;
    }

    public function index(): View
    {
        $statistics = $this->userRepositories->statics();
        return view('dashboard', compact('statistics'));
    }
}
