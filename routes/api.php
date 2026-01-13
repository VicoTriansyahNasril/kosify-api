<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\BoardingHouseController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/explore', [PublicController::class, 'index']);
Route::get('/explore/{slug}', [PublicController::class, 'show']);
Route::get('/boarding-houses/{id}/reviews', [ReviewController::class, 'index']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/bookings', [App\Http\Controllers\Api\BookingController::class, 'store']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/delete', [AuthController::class, 'destroy']);

    Route::post('/boarding-houses/{id}/reviews', [ReviewController::class, 'store']);

    Route::apiResource('boarding-houses', BoardingHouseController::class);

    Route::get('/boarding-houses/{boarding_house}/rooms', [RoomController::class, 'index']);
    Route::apiResource('rooms', RoomController::class)->except(['index', 'show']);

    Route::get('/boarding-houses/{boarding_house}/tenants', [TenantController::class, 'index']);
    Route::post('/tenants', [TenantController::class, 'store']);
    Route::put('/tenants/{tenant}', [TenantController::class, 'update']);
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy']);

    Route::get('/boarding-houses/{boarding_house}/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    Route::get('/boarding-houses/{boarding_house}/bookings', [App\Http\Controllers\Api\BookingController::class, 'index']);
    Route::patch('/bookings/{booking}/status', [App\Http\Controllers\Api\BookingController::class, 'updateStatus']);

    Route::get('/dashboard/stats', [DashboardController::class, 'index']);

    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/owners', [UserController::class, 'index']);
        Route::delete('/owners/{user}', [UserController::class, 'destroy']);
    });
});