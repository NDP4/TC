<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LevelUpController;
use App\Http\Controllers\SocialAuthController;
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

    // Google OAuth routes (public)
    Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

    // Protected routes (require authentication)
    Route::middleware(['jwt.auth'])->group(function () {
        // Authentication management
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // User management
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::post('/users/{id}/update', [UserController::class, 'update']); // Alternative for multipart forms
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);

        // Level up requests
        Route::post('/level-up-request', [LevelUpController::class, 'store']);
        Route::get('/level-up-request/{id}', [LevelUpController::class, 'show']);
        Route::get('/level-up-requests', [LevelUpController::class, 'index']);
        Route::post('/level-up-request/{id}/verify', [LevelUpController::class, 'verify'])->middleware('role:admin,verifikator');
        Route::get('/my-level-up-requests', [LevelUpController::class, 'userRequests']);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    });
});
