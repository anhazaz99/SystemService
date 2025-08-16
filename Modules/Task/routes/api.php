<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use Modules\Task\Http\Controllers\Calendar\CalendarController;

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

// Calendar API Routes
Route::prefix('calendar')->group(function () {
    // Basic CRUD routes
    Route::get('/', [CalendarController::class, 'index']);
    Route::post('/', [CalendarController::class, 'store']);
    Route::get('/{calendar}', [CalendarController::class, 'show']);
    Route::put('/{calendar}', [CalendarController::class, 'update']);
    Route::delete('/{calendar}', [CalendarController::class, 'destroy']);
    
    // Calendar view routes
    Route::get('/view', [CalendarController::class, 'view']);
    
    // Recurring events routes
    Route::post('/recurring', [CalendarController::class, 'createRecurring']);
    Route::put('/recurring/{calendar}', [CalendarController::class, 'updateRecurring']);
    
    // Import/Export routes
    Route::post('/export', [CalendarController::class, 'export']);
    Route::post('/import', [CalendarController::class, 'import']);
    
    // Sync routes
    Route::post('/sync', [CalendarController::class, 'sync']);
    
    // Statistics and analysis routes
    Route::get('/statistics', [CalendarController::class, 'statistics']);
    Route::get('/conflicts', [CalendarController::class, 'conflicts']);
    
    // Reminders routes
    Route::get('/reminders', [CalendarController::class, 'reminders']);
    Route::post('/reminders', [CalendarController::class, 'setReminder']);
    
    // Event filtering routes
    Route::get('/events/by-date', [CalendarController::class, 'eventsByDate']);
    Route::get('/events/by-range', [CalendarController::class, 'eventsByRange']);
    Route::get('/events/recurring', [CalendarController::class, 'recurringEvents']);
    Route::get('/events/upcoming', [CalendarController::class, 'upcomingEvents']);
    Route::get('/events/overdue', [CalendarController::class, 'overdueEvents']);
    Route::get('/events/by-type', [CalendarController::class, 'eventsByType']);
    Route::get('/events/count-by-status', [CalendarController::class, 'eventsCountByStatus']);
});

// Protected routes (if needed)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Route::apiResource('tasks', TaskController::class)->names('task');
});
