<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Base API v1 routes
Route::prefix('v1')->group(function () {
    // Authentication routes (public)
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/users/register', [AuthController::class, 'register']); // Alternative endpoint as per spec

    // Protected routes (require authentication)
    Route::middleware(['auth:api'])->group(function () {
        // Authentication management
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // User management
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);
    });
});
