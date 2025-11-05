<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrganizerRequestController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    //auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    //email
    Route::get('/email/verify', [VerificationController::class, 'verify']);
    Route::post('/email/resend', [VerificationController::class, 'resend']);

    //password
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

    //event
    Route::get('/event', [EventController::class, 'index']);
    Route::get('/event/{slug}', [EventController::class, 'showBySlug']);
});

Route::middleware(['api', 'auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/update', [PasswordResetController::class, 'updatePassword']);
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'update']);
    });

    Route::group(['prefix' => 'event', 'middleware' => ['isEmailVerified', 'isPhoneVerified', 'role:' . RoleEnum::ORGANIZER->value . ',' . RoleEnum::ADMIN->value]], function () {
        Route::post('/', [EventController::class, 'store']);
        Route::post('/{event}', [EventController::class, 'update']);
        Route::post('/{event}/cancel', [EventController::class, 'cancel']);
    });

    // Route::post('/phone/send-otp', [PhoneVerificationController::class, 'sendOtp'])->middleware(['isEmailVerified']);
    // Route::post('/phone/verify-otp', [PhoneVerificationController::class, 'verifyOtp'])->middleware(['isEmailVerified']);
    Route::post('/phone/verify', [PhoneVerificationController::class, 'verify'])->middleware(['isEmailVerified']);

    Route::group(['prefix' => 'organizer-request', 'middleware' => ['isEmailVerified', 'isPhoneVerified']], function () {
        Route::post('/', [OrganizerRequestController::class, 'store']);
        Route::get('/{organizerRequest}', [OrganizerRequestController::class, 'show']);

        Route::get('/', [OrganizerRequestController::class, 'index'])->middleware('role:' . RoleEnum::ADMIN->value);
        Route::post('/{organizerRequest}/approve', [OrganizerRequestController::class, 'approve'])->middleware('role:' . RoleEnum::ADMIN->value);
        Route::post('/{organizerRequest}/reject', [OrganizerRequestController::class, 'reject'])->middleware('role:' . RoleEnum::ADMIN->value);
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
