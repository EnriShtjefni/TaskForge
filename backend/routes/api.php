<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationMemberController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
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

//Route::prefix('auth')->group(function () {
//    Route::post('register', [AuthController::class, 'register']);
//    Route::post('login', [AuthController::class, 'login']);
//
//    Route::middleware('auth:sanctum')->group(function () {
//        Route::post('logout', [AuthController::class, 'logout']);
//        Route::get('me', [AuthController::class, 'me']);
//    });
//});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('organizations', OrganizationController::class);

    Route::get('organizations/{organization}/projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::put('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    Route::post('/organizations/{organization}/members', [OrganizationMemberController::class, 'store']);
    Route::put('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'update']);
    Route::delete('/organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'destroy']);

    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store']);
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy']);

    Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});
