<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// Task API Routes
Route::prefix('tasks')->group(function () {
    // Basic CRUD routes
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/', [TaskController::class, 'store']);
    Route::get('/{id}', [TaskController::class, 'show']);
    Route::put('/{id}', [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);
    
    // File management routes
    Route::get('/{taskId}/files/{fileId}/download', [TaskController::class, 'downloadFile']);
    Route::delete('/{taskId}/files/{fileId}', [TaskController::class, 'deleteFile']);
});

// Alternative: Use resource routes (uncomment if needed)
// Route::apiResource('tasks', TaskController::class)->names('task');

// Protected routes (if needed)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('tasks', TaskController::class)->names('task');
});
