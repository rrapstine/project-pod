<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::post('/register', AuthController::class . '@register');
Route::post('/login', AuthController::class . '@login');
Route::post('/logout', AuthController::class . '@logout');
