<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends BaseController
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        $user->notify(new ResetPasswordNotification($token));

        return $this->successResponse('Password rest link sent successfully.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return $this->errorResponse('Invalid or expired token');
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token after successful reset
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->successResponse('Password reset successfully');
    }

    function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'], // expects new_password_confirmation
        ]);

        $user = $request->user();

        // check old password
        if (!Hash::check($request->old_password, $user->password)) {
            return $this->errorResponse('Old password is incorrect', 422);
        }

        if ($request->old_password == $request->password) {
            return $this->errorResponse("Old password and new password can't be matched", 422);
        }

        // update new password
        $user->password = Hash::make($request->password);
        $user->save();

        return $this->successResponse('Password updated successfully');
    }
}
