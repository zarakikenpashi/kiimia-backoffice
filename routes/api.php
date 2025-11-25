<?php

use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Tes ressources API
    // Route::apiResource('posts', PostController::class);
});