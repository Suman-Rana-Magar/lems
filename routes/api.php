<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\OrganizerRequestController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ResourcesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    //auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    //email
    Route::get('/email/verify', [VerificationController::class, 'verify']);
    Route::post('/email/resend', [VerificationController::class, 'resend']);

    //password
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

    //event
    Route::get('/event', [EventController::class, 'index']);
    Route::get('/event/{slug}', [EventController::class, 'showBySlug']);

    //resources
    Route::get('/resources/address', [ResourcesController::class, 'address']);
    Route::get('/resources/categories', [ResourcesController::class, 'categories']);
    // Route::get('/resources/enums', [ResourcesController::class, 'enums']);

    // Storage route with CORS headers and proper Content-Type to fix ORB blocking
    // Handle OPTIONS preflight requests
    Route::options('/storage/{path}', function () {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->header('Access-Control-Max-Age', '86400');
    })->where('path', '.*');
    
    Route::get('/storage/{path}', function ($path) {
        $file = storage_path('app/public/' . $path);
        
        if (!file_exists($file)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        // Detect MIME type based on file extension
        $mimeType = mime_content_type($file);
        if (!$mimeType) {
            // Fallback MIME types for common extensions
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'pdf' => 'application/pdf',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        }
        
        // THE FIX - Add these headers to prevent ORB blocking and set correct Content-Type
        // Important: Set all headers explicitly for ngrok and cross-browser compatibility
        $response = response()->file($file);
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Max-Age', '86400');
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin'); // CRITICAL for ORB!
        $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none'); // ALSO NEEDED!
        $response->headers->set('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
        
        return $response;
    })->where('path', '.*');
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
        Route::get('/my', [OrganizerRequestController::class, 'myRequests']);
        Route::get('/{organizerRequest}', [OrganizerRequestController::class, 'show']);

        Route::get('/', [OrganizerRequestController::class, 'index'])->middleware('role:' . RoleEnum::ADMIN->value);
        Route::post('/{organizerRequest}/approve', [OrganizerRequestController::class, 'approve'])->middleware('role:' . RoleEnum::ADMIN->value);
        Route::post('/{organizerRequest}/reject', [OrganizerRequestController::class, 'reject'])->middleware('role:' . RoleEnum::ADMIN->value);
    });

    Route::group(['prefix' => 'event-registration', 'middleware' => 'isEmailVerified'], function () {
        Route::get('/', [EventRegistrationController::class, 'index'])->middleware('role:' . RoleEnum::ADMIN->value);
        Route::get('/my', [EventRegistrationController::class, 'myList']);
        Route::post('/', [EventRegistrationController::class, 'store']);
        Route::post('/{eventRegistration}/cancel', [EventRegistrationController::class, 'cancel']);
        Route::get('/{eventRegistration}', [EventRegistrationController::class, 'show']);
        Route::get('/{eventRegistration}/ticket', [EventRegistrationController::class, 'downloadTicket']);
    });
});

Route::middleware(['api', 'auth:api', 'role:' . RoleEnum::ADMIN->value])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::post('/relation', [CategoryController::class, 'storeRelation']);
        Route::get('/relation-prompt', [CategoryController::class, 'getRelationPrompt']);
        Route::post('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'delete']);
    });
});
