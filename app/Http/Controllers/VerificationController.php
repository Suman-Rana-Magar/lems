<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerificationController extends BaseController
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string'
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_token', $request->token)
            ->first();

        if (!$user) {
            return $this->errorResponse('Invalid verification link.');
        }

        $user->markEmailAsVerified();

        return $this->successResponse('Email verified successfully.');
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified.');
        }

        $user->verification_token = Str::random(60);
        $user->save();

        $user->notify(new VerifyEmailNotification($user->verification_token));

        return $this->successResponse('Verification link resent.');
    }
}
