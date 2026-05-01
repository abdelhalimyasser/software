<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobRequisitionController;

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

// Private Routes for Job Requisitions
Route::middleware('auth:sanctum')->prefix('v1/jobs')->group(function () {
    Route::get('/', [JobRequisitionController::class, 'index']);
    Route::get('/{job}', [JobRequisitionController::class, 'show']);
    Route::post('/', [JobRequisitionController::class, 'store']);
    Route::post('/{job}/approve', [JobRequisitionController::class, 'approve']);
    Route::post('/{job}/reject', [JobRequisitionController::class, 'reject']);
});

// AJAX Routes for Notifications
Route::middleware('auth:sanctum')->prefix('v1/notifications')->group(function () {
    Route::get('/unread', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'notifications' => $request->user()->unreadNotifications
        ], 200);
    });

    Route::post('/{id}/read', function ($id, \Illuminate\Http\Request $request) {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json([
            'message' => 'Notification marked as read'
        ], 200);
    });
});
