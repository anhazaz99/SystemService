<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\AuthController;
use Modules\Auth\app\Http\Controllers\StudentController;
use Modules\Auth\app\Http\Controllers\LecturerController;

// Auth routes (không cần authentication)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/student', [AuthController::class, 'loginStudent']);
Route::post('/login/lecturer', [AuthController::class, 'loginLecturer']);

// Auth routes (cần JWT authentication)
Route::middleware(['jwt'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Student routes - Chỉ admin mới có thể quản lý
Route::middleware(['jwt', 'admin'])->group(function () {
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::put('/students/{id}', [StudentController::class, 'update']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
});

// Lecturer routes - Chỉ admin mới có thể quản lý
Route::middleware(['jwt', 'admin'])->group(function () {
    Route::get('/lecturers', [LecturerController::class, 'index']);
    Route::post('/lecturers', [LecturerController::class, 'store']);
    Route::get('/lecturers/{id}', [LecturerController::class, 'show']);
    Route::put('/lecturers/{id}', [LecturerController::class, 'update']);
    Route::delete('/lecturers/{id}', [LecturerController::class, 'destroy']);
    Route::patch('/lecturers/{id}/admin-status', [LecturerController::class, 'updateAdminStatus']);
});

// Routes cho sinh viên - Chỉ xem thông tin của mình
Route::middleware(['jwt'])->group(function () {
    Route::get('/student/profile', [StudentController::class, 'showOwnProfile']);
    Route::put('/student/profile', [StudentController::class, 'updateOwnProfile']);
});

// Routes cho giảng viên - Chỉ xem thông tin của mình
Route::middleware(['jwt'])->group(function () {
    Route::get('/lecturer/profile', [LecturerController::class, 'showOwnProfile']);
    Route::put('/lecturer/profile', [LecturerController::class, 'updateOwnProfile']);
});
