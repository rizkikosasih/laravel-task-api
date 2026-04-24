<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);

    Route::post('/projects', [ProjectController::class, 'store'])->middleware(
        'permission:create project',
    );

    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    Route::put('/projects/{id}', [ProjectController::class, 'update'])->middleware(
        'permission:update project',
    );

    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->middleware(
        'permission:delete project',
    );
});
