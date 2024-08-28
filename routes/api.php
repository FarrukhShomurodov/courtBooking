<?php

use App\Http\Controllers\Admin\Api\BookingController;
use App\Http\Controllers\Admin\Api\BotUserController;
use App\Http\Controllers\Admin\Api\CourtController;
use App\Http\Controllers\Admin\Api\ScheduleController;
use App\Http\Controllers\Admin\Api\SportTypeController;
use App\Http\Controllers\Admin\Api\StadiumController;
use App\Http\Controllers\Admin\Api\UserController;
use Illuminate\Support\Facades\Route;

// bot user
Route::post('/has-bot-user', [\App\Http\Controllers\Telegram\Api\UserController::class, 'hasUser']);
Route::get('/bot-user/{chat_id}', [\App\Http\Controllers\Telegram\Api\UserController::class, 'getUserByChatId']);

// Is active
Route::put('/bot-users/{botUser}/is-active', [BotUserController::class, 'isActive']);
Route::put('/stadium/{stadium}/is-active', [StadiumController::class, 'isActive']);
Route::put('/courts/{court}/is-active', [CourtController::class, 'isActive']);

// schedule
Route::post('/fetch-schedule-by-date', [ScheduleController::class, 'fetchByDate']);

// Delete image
Route::delete('/delete/sport_type_photos/{photoPath}/{sportType}', [SportTypeController::class, 'deletePhoto']);
Route::delete('/delete/stadium_photos/{photoPath}/{sportType}', [StadiumController::class, 'deletePhoto']);
Route::delete('/delete/court_photos/{photoPath}/{sportType}', [CourtController::class, 'deletePhoto']);
Route::delete('/delete/user_avatar/{user}', [UserController::class, 'deletePhoto']);

// Price by time
Route::post('/price-by-time', [ScheduleController::class, 'priceByTime']);

// Court
Route::get('/get-schedule', [CourtController::class, 'getSchedule']);

// Booking
Route::get('/booking/{booking}', [BookingController::class, 'show']);
Route::post('/booking', [BookingController::class, 'store']);
Route::put('/booking/{booking}', [BookingController::class, 'update']);
Route::post('/booking-by-date', [BookingController::class, 'showByDate']);

// Sport Type
Route::get('/stadium-spot-type/{stadium}', [SportTypeController::class, 'byStadium']);
