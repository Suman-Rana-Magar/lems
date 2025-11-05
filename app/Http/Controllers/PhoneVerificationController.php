<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhoneVerification;
use App\Models\OrganizerRequest;
use Twilio\Rest\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PhoneVerificationController extends BaseController
{
    public function verify(Request $request)
    {
        $request->validate([
            'phone_no' => ['required', 'regex:/^(97|98)\d{8}$/'],
        ], [
            'phone_no.required' => 'Phone number is required.',
            'phone_no.regex' => 'Please enter a valid Nepali phone number starting with 97 or 98 and exactly 10 digits long.',
        ]);

        $otp = rand(100000, 999999);
        $phone = $request->phone_no;

        $user = Auth::user();
        // Store or update OTP in database
        $user->update([
            'phone_no' => $phone,
            'phone_no_verified_at' => now(),
            'otp' => null
        ]);

        return $this->successResponse('Phone no. verified successfully successfully.');
    }
    public function sendOtp(Request $request)
    {
        if (Auth::user()->phone_no_verified_at) return $this->errorResponse("Phone no. already verified");
        $request->validate([
            'phone_no' => ['required', 'regex:/^(97|98)\d{8}$/'],
        ], [
            'phone_no.required' => 'Phone number is required.',
            'phone_no.regex' => 'Please enter a valid Nepali phone number starting with 97 or 98 and exactly 10 digits long.',
        ]);

        $otp = rand(100000, 999999);
        $phone = $request->phone_no;

        $user = Auth::user();
        // Store or update OTP in database
        $user->update([
            'phone_no' => $phone,
            'otp' => $otp
        ]);

        // Send OTP using Twilio (test mode)
        $client = new Client(config('services.twilio.sid'), config('services.twilio.token'));

        try {
            $client->messages->create(
                '+977' . $phone, // e.g., "+9779812345678" for real case
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Your verification code for " . config('app.name') . " is: $otp",
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send OTP');
        }

        return $this->successResponse('OTP sent successfully.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_no' => ['required', 'regex:/^(97|98)\d{8}$/'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();

        if ($user->phone_no_verified_at) return $this->errorResponse('Phone no. already verified');

        if ((int) $user->otp !== $request->otp) return $this->errorResponse('Invalid OTP');

        // Mark verified in organizer_requests
        $user->update([
            'phone_no_verified_at' => now(),
            'OTP' => null
        ]);

        return $this->successResponse('Phone number verified successfully');
    }
}
