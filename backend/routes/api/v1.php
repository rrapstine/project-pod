<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('user', [UserController::class, 'show']);
    Route::put('user', [UserController::class, 'update']);
    Route::delete('user', [UserController::class, 'destroy']);

    // Workspaces
    Route::apiResource('workspaces', WorkspaceController::class);

    // Projects
    Route::get('workspaces/{workspace}/projects', [ProjectController::class, 'index']); // nested read
    Route::post('workspaces/{workspace}/projects', [ProjectController::class, 'store']); // nested create
    Route::apiResource('projects', ProjectController::class)->only(['index', 'show', 'update', 'destroy']); // flat operations

    // Tasks
    Route::get('projects/{project}/tasks', [TaskController::class, 'index']); // nested read
    Route::post('projects/{project}/tasks', [TaskController::class, 'store']); // nested create
    Route::apiResource('tasks', TaskController::class)->only(['index', 'show', 'update', 'destroy']); // flat operations
});
