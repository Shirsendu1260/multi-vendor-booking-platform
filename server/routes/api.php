<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    // Test routes
    Route::get('ping', function() {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Hello, world!'
        ]);
    });

    // Auth related routes
    Route::prefix('auth')->group(function() {
        // Public routes
        Route::middleware('throttle:20,1')->group(function() { // rate limit: 20 attempts per 1 minute
            Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        });
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.login');

        // Authenticated routes
        Route::middleware(['auth:api', 'throttle:30,1'])->group(function() {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('/sessions', [AuthController::class, 'activeSessions'])->name('auth.activeSessions');
            Route::delete('/sessions/{uuid}', [AuthController::class, 'revokeSession'])
                ->whereUuid('uuid') // checks 'uuid' param matches UUID format, if not then returns 404 automatically
                ->name('auth.revokeSession');
        });
    });
});