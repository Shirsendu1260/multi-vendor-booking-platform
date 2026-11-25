<?php

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
});