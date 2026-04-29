<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Routes for Candidates
Route::prefix('v1/public/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'registerCandidate']);
});

// Public Auth Routes (no authentication required)
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Private Routes (authentication required)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
});

// Private Routes for Employees
Route::prefix('v1/private/auth')->group(function () {
    Route::post('/register-new-employee', [AuthController::class, 'registerEmployee']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::put('/update-employee/{id}', [AuthController::class, 'updateEmployee']);
});
