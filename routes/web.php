<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\BotUserController;
use App\Http\Controllers\Dashboard\CourtController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\SportTypeController;
use App\Http\Controllers\Dashboard\StadiumController;
use App\Http\Controllers\Dashboard\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/login', [AuthController::class, 'showLoginForm']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::group(['middleware' => 'auth'], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::group(['middleware' => 'role:admin'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('/users', UserController::class);
        Route::resource('/bot-users', BotUserController::class);
        Route::resource('/sport-types', SportTypeController::class);

    });

    Route::group(['middleware' => 'role:owner stadium|admin'], function () {
        Route::resource('/stadiums', StadiumController::class);
        Route::resource('/courts', CourtController::class);
    });
});
