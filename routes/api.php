<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Load routes từ các modules
if (file_exists(__DIR__ . '/../Modules/Auth/routes/api.php')) {
    require __DIR__ . '/../Modules/Auth/routes/api.php';
}

if (file_exists(__DIR__ . '/../Modules/Task/routes/api.php')) {
    require __DIR__ . '/../Modules/Task/routes/api.php';
}
