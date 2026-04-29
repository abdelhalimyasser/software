<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Route;
use App\Enums\UserRole;


// Public Routes
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Private Routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    Route::middleware('role:' . UserRole::CANDIDATE->value)->group(function () {
        Route::post('/apply-job', [JobController::class, 'apply']);
        Route::get('/my-applications', [JobController::class, 'myApplications']);
    });

    // 🔴 مدير الـ HR بس (يقدر يكريت انترفيوهات ويضيف موظفين)
    Route::middleware('role:' . UserRole::HR_ADMIN->value)->group(function () {
        Route::post('/hr/create-job', [JobController::class, 'store']);
        Route::post('/hr/add-employee', [EmployeeController::class, 'store']);
    });

    // 🔴 الـ Department Manager بس (يقدر يوافق على طلبات التعيين)
    Route::middleware('role:' . UserRole::DEPARTMENT_MANGER->value)->group(function () {
        Route::post('/manager/approve-requisition', [RequisitionController::class, 'approve']);
    });

    // 🔴 الـ Interviewers (الأساسي والـ Shadow) يقدروا يحطوا تقييم
    Route::middleware('role:' . UserRole::INTERVIEWER->value . ',' . UserRole::SHADOW_INTERVIEWER->value)->group(function () {
        Route::post('/interview/submit-feedback', [InterviewController::class, 'submitFeedback']);
    });

    // 🔴 مسارات مشتركة (أي حد مسجل دخول يقدر يعدل بروفايله)
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
});
