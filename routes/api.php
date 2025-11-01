<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('/email/verify', [VerificationController::class, 'verify']);
    Route::post('/email/resend', [VerificationController::class, 'resend']);
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
});

Route::middleware(['api', 'auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/update', [PasswordResetController::class, 'updatePassword']);
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'update']);
    });
});

Route::middleware(['api', 'auth:api', 'role:' . RoleEnum::ADMIN->value])->group(function () {
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
    });
});

Route::middleware(['api', 'auth:api', 'isEmailVerified'])->group(function () {
    Route::get('/test', [AuthController::class, 'test']);
});

// Route::middleware(['auth', 'verified'])->group(function() {
//     Route::get('/dashboard', [DashboardController::class, 'index']);
// });
