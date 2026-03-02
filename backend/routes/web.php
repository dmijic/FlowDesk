<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])
    ->name('auth.login');

Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])
    ->name('auth.forgot-password');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
});

Route::get('/', [SystemController::class, 'status']);
Route::get('/health', [SystemController::class, 'health'])->name('health');
