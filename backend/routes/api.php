<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are stateless and protected via Sanctum.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('organizations', OrganizationController::class);

    Route::apiResource('projects', ProjectController::class);

    Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});
