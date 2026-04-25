<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    /*
    |--------------------------
    | PROJECT MODULE
    |--------------------------
    */
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');

        Route::post('/', [ProjectController::class, 'store'])
            ->name('projects.create')
            ->middleware('permission:create project');

        Route::get('/{id}', [ProjectController::class, 'show'])
            ->name('projects.detail')
            ->where('id', '[0-9]+');

        Route::put('/{id}', [ProjectController::class, 'update'])
            ->name('projects.update')
            ->where('id', '[0-9]+')
            ->middleware('permission:update project');

        Route::delete('/{id}', [ProjectController::class, 'destroy'])
            ->name('projects.delete')
            ->where('id', '[0-9]+')
            ->middleware('permission:delete project');
    });

    /*
    |--------------------------
    | TASK MODULE
    |--------------------------
    */
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

        Route::get('/{id}', [TaskController::class, 'show'])
            ->name('tasks.detail')
            ->where('id', '[0-9]+');

        Route::post('/', [TaskController::class, 'store'])
            ->name('tasks.create')
            ->middleware('permission:create task');

        Route::put('/{id}', [TaskController::class, 'update'])
            ->name('tasks.update')
            ->where('id', '[0-9]+')
            ->middleware('permission:update task');

        Route::patch('/{id}/status', [TaskController::class, 'updateStatus'])
            ->name('tasks.status')
            ->where('id', '[0-9]+');

        Route::delete('/{id}', [TaskController::class, 'destroy'])
            ->name('tasks.delete')
            ->where('id', '[0-9]+')
            ->middleware('permission:delete task');
    });

    /*
    |--------------------------
    | COMMENTS MODULE
    |--------------------------
    */
    Route::prefix('tasks/{task}/comments')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('comments.index');

        Route::post('/', [CommentController::class, 'store'])
            ->name('comments.store')
            ->middleware('permission:create task comment');
    });

    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])
        ->name('comments.destroy')
        ->where('id', '[0-9]+')
        ->middleware('permission:delete task comment');
});
