<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BotUserController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SportTypeController;
use App\Http\Controllers\Api\StadiumController;
use Illuminate\Support\Facades\Route;

// Is active
Route::put('/bot-users/{botUser}/is-active', [BotUserController::class, 'isActive']);
Route::put('/stadium/{stadium}/is-active', [StadiumController::class, 'isActive']);
Route::put('/courts/{court}/is-active', [CourtController::class, 'isActive']);

// Court
Route::get('/courts/{court}', [CourtController::class, 'show']);

// Delete image
Route::delete('/delete/sport_type_photos/{photoPath}/{sportType}', [SportTypeController::class, 'deletePhoto']);
Route::delete('/delete/stadium_photos/{photoPath}/{sportType}', [StadiumController::class, 'deletePhoto']);
Route::delete('/delete/court_photos/{photoPath}/{sportType}', [CourtController::class, 'deletePhoto']);

// Price by time
Route::post('/price-by-time', [ScheduleController::class, 'priceByTime']);

// Booking
Route::get('/booking/{booking}', [BookingController::class, 'show']);
Route::post('/booking-by-date', [BookingController::class, 'showByDate']);
