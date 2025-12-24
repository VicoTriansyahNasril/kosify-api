<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PublicController;
use Illuminate\Support\Facades\Route;

// --- PUBLIC ROUTES (Tanpa Login) ---
Route::get('/explore', [PublicController::class, 'index']);
Route::get('/explore/{slug}', [PublicController::class, 'show']);

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Harus Login)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard Stats
    Route::get('/dashboard/stats', [App\Http\Controllers\Api\DashboardController::class, 'index']);

    // Fitur Owner
    Route::apiResource('boarding-houses', \App\Http\Controllers\Api\BoardingHouseController::class);
    Route::get('/boarding-houses/{boarding_house}/rooms', [App\Http\Controllers\Api\RoomController::class, 'index']);
    Route::apiResource('rooms', App\Http\Controllers\Api\RoomController::class)->except(['index', 'show']);

    // Route Tenant
    Route::get('/boarding-houses/{boarding_house}/tenants', [App\Http\Controllers\Api\TenantController::class, 'index']);
    Route::post('/tenants', [App\Http\Controllers\Api\TenantController::class, 'store']);
    Route::delete('/tenants/{tenant}', [App\Http\Controllers\Api\TenantController::class, 'destroy']);

    // Transactions
    Route::get('/boarding-houses/{boarding_house}/transactions', [App\Http\Controllers\Api\TransactionController::class, 'index']);
    Route::post('/transactions', [App\Http\Controllers\Api\TransactionController::class, 'store']);
    Route::patch('/transactions/{transaction}/status', [App\Http\Controllers\Api\TransactionController::class, 'updateStatus']);

    // Fitur Admin
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/owners', [App\Http\Controllers\Api\Admin\UserController::class, 'index']);
    });
});