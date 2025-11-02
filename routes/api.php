<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
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
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'update']);
    });

    Route::group(['prefix' => 'event'], function () {
        Route::get('/', [EventController::class, 'index']);
        Route::get('/{slug}', [EventController::class, 'showBySlug']);
        Route::post('/', [EventController::class, 'store'])->middleware('role:' . RoleEnum::ORGANIZER->value);
    });
});

Route::middleware(['api', 'auth:api', 'role:' . RoleEnum::ADMIN->value])->group(function () {
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::post('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'delete']);
    });
});

Route::middleware(['api', 'auth:api', 'isEmailVerified'])->group(function () {
    Route::get('/test', [AuthController::class, 'test']);
});

Route::get('/testing', [AuthController::class, 'test']);
