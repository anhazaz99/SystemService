<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\AuthController;
use Modules\Auth\app\Http\Controllers\StudentController;
use Modules\Auth\app\Http\Controllers\LecturerController;
use Modules\Auth\app\Http\Middleware\JwtMiddleware;

// Auth routes (không cần authentication)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/student', [AuthController::class, 'loginStudent']);
Route::post('/login/lecturer', [AuthController::class, 'loginLecturer']);

// Auth routes (cần JWT authentication)
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Student routes (cần JWT authentication)
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::put('/students/{id}', [StudentController::class, 'update']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
});

// Lecturer routes (cần JWT authentication)
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('/lecturers', [LecturerController::class, 'index']);
    Route::post('/lecturers', [LecturerController::class, 'store']);
    Route::get('/lecturers/{id}', [LecturerController::class, 'show']);
    Route::put('/lecturers/{id}', [LecturerController::class, 'update']);
    Route::delete('/lecturers/{id}', [LecturerController::class, 'destroy']);
    Route::patch('/lecturers/{id}/admin-status', [LecturerController::class, 'updateAdminStatus']);
});
