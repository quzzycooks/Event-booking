<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\OnlyAttendee;
use App\Http\Middleware\OnlyOrganizer;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Public event listing
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'get']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [UserController::class, 'index']);

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::group([
        'prefix' => '/events',
        'middleware' => OnlyOrganizer::class
    ], function () {
        Route::post('/', [EventController::class, 'create']);
        Route::get('/{id}/bookings', [EventController::class, 'bookings']);

        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'delete']);
    });

    Route::group([
        'prefix' => '/bookings',
        'middleware' => OnlyAttendee::class
    ], function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'create']);

        Route::get('/{id}', [BookingController::class, 'get']);
        Route::delete('/{id}', [BookingController::class, 'delete']);
    });
});
