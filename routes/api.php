<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BotUserController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\SportTypeController;
use App\Http\Controllers\Api\StadiumController;
use Illuminate\Support\Facades\Route;


Route::put('bot-users/{botUser}/is-active', [BotUserController::class, 'isActive']);
Route::put('stadium/{stadium}/is-active', [StadiumController::class, 'isActive']);
Route::put('courts/{court}/is-active', [CourtController::class, 'isActive']);
Route::get('courts/{court}', [CourtController::class, 'show']);

Route::delete('/delete/sport_type_photos/{photoPath}/{sportType}', [SportTypeController::class, 'deletePhoto']);
Route::delete('/delete/stadium_photos/{photoPath}/{sportType}', [StadiumController::class, 'deletePhoto']);
Route::delete('/delete/court_photos/{photoPath}/{sportType}', [CourtController::class, 'deletePhoto']);


Route::get('days-by-court/{court}', [BookingController::class, 'getDaysByCourt'])->name('bookings.days-by-court');
Route::get('hours-by-day/{day}', [BookingController::class, 'getHoursByDay'])->name('bookings.hours-by-day');
