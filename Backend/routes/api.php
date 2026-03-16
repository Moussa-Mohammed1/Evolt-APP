<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChargingSessionController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\StationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/stations', [StationController::class, 'index']);
 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/charging-sessions/history', [ChargingSessionController::class, 'history']);
    Route::middleware('can:manage-stations')->group(function () {
        Route::post('/stations', [StationController::class, 'store']);
        Route::patch('/stations/{station}', [StationController::class, 'update']);
        Route::delete('/stations/{station}', [StationController::class, 'destroy']);
        Route::get('/admin/stats', [StationController::class, 'globalStats']);
        Route::get('/stations/{station}/stats', [StationController::class, 'stats']);
    });

    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::patch('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);
    Route::post('/charge', [ChargingSessionController::class, 'store']);
    Route::get('/stations/{station}', [StationController::class, 'show']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});