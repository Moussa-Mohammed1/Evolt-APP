<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\StationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/stations', [StationController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::patch('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);

    Route::get('/stations/{station}', [StationController::class, 'show']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});