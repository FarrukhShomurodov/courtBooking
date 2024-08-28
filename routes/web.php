<?php

use App\Http\Controllers\Admin\Web\BookingController;
use App\Http\Controllers\Admin\Web\BotUserController;
use App\Http\Controllers\Admin\Web\CourtController;
use App\Http\Controllers\Admin\Web\DashboardController;
use App\Http\Controllers\Admin\Web\SportTypeController;
use App\Http\Controllers\Admin\Web\StadiumController;
use App\Http\Controllers\Admin\Web\StatisticsController;
use App\Http\Controllers\Admin\Web\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Telegram\FindzController;
use App\Http\Controllers\Telegram\PaycomController;
use App\Http\Controllers\Telegram\TelegramController;
use Illuminate\Support\Facades\Route;

// Telegram

Route::get('/login', [AuthController::class, 'showLoginForm']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/confirm-owner', [AuthController::class, 'OwnerConfirmation'])->name('owner.confirmation');
Route::get('/set-lang/{locale}/{botUser?}', [DashboardController::class, 'setLocale'])->name('set.lang');

Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::group(['middleware' => 'role:admin'], function () {
        Route::resource('/users', UserController::class);
        Route::resource('/bot-users', BotUserController::class);
        Route::resource('/sport-types', SportTypeController::class);
    });

    Route::group(['middleware' => 'role:owner stadium|admin'], function () {
        Route::resource('/stadiums', StadiumController::class)->names('stadiums');
        Route::resource('/courts', CourtController::class);
        Route::get('/all-bookings', [BookingController::class, 'fetchAllBooking'])->name('all-bookings');

        // Statistics
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('statistics')->name('statistics.')->group(function () {
            Route::get('/stadiums', [StatisticsController::class, 'stadiums'])->name('stadiums');
            Route::get('/courts', [StatisticsController::class, 'courts'])->name('courts');
            Route::get('/sport-type', [StatisticsController::class, 'sportType'])->name('sport.type');
            Route::get('/export', [StatisticsController::class, 'exportStatistics'])->name('export');
            Route::get('/stadiums/export', [StatisticsController::class, 'exportStadiumsStatistics'])->name('stadiums.export');
            Route::get('/courts/export', [StatisticsController::class, 'exportCourtsStatistics'])->name('courts.export');
            Route::get('/sport-type/export', [StatisticsController::class, 'exportSportTypeStatistics'])->name('sport.type.export');
        });
    });

    Route::resource('/bookings', BookingController::class);
});


Route::prefix('telegram')->group(function () {
    Route::get('/webhook', function () {
        $telegram = new \Telegram\Bot\Api(config('telegram.bot_token'));
        $hook = $telegram->setWebhook(['url' => env('TELEGRAM_WEBHOOK_URL')]);
        return dd($hook);
    });

    Route::post('/webhook', [TelegramController::class, 'handleWebhook']);

    // Main pages
    Route::get('/webapp', [FindzController::class, 'courts'])->name('webapp');
    Route::get('/courts/sport-type/{sportType}', [FindzController::class, 'courtsBySportType'])->name('findz.courts.filter.sport.type');
    Route::get('/coaches/sport-type/{sportType}', [FindzController::class, 'coachesBySportType'])->name('findz.coaches.filter.sport.type');
    Route::get('/filter/{sportType}', [FindzController::class, 'filter'])->name('findz.filter');

    // Show pages
    Route::get('/show/coach/{coach}', [FindzController::class, 'coachShow'])->name('findz.show.coach');
    Route::get('/show/court/{court}', [FindzController::class, 'courtShow'])->name('findz.show.court');


    Route::get('/book', [FindzController::class, 'book'])->name('findz.book');
    Route::get('/book/edit/{booking}', [FindzController::class, 'bookUpdate'])->name('book.edit');
    Route::get('/mybookings', [FindzController::class, 'myBookings'])->name('findz.mybookings');
});


Route::post('/paycom', [PaycomController::class, 'handleRequest']);
