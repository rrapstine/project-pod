<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('workspaces', WorkspaceController::class);
    Route::get('workspaces/{workspace}/projects', WorkspaceController::class.'@getProjects');

    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/{project}/tasks', ProjectController::class.'@getTasks');

    Route::apiResource('tasks', TaskController::class);
});
