<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogTestController;

Route::get('/', function () {
    return view('welcome');
});

// Log generation endpoints for ELK stack testing
Route::prefix('api/logs')->group(function () {
    Route::get('/generate', [LogTestController::class, 'generateLogs']);
    Route::get('/batch', [LogTestController::class, 'batchLogs']);
    Route::get('/errors', [LogTestController::class, 'errorScenarios']);
});

