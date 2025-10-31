<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Upload;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService
{
    use Upload;

    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->orWhere('username', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password))
            return ('Invalid login credentials.');
        $user->token = $user->createToken('Personal Access Token')->accessToken;
        return $user->load('interests');
    }

    public function register(array $data)
    {
        DB::beginTransaction();
        try {
            if ($data['profile_picture'] && is_file($data['profile_picture'])) {
                $path = $this->UploadFile($data['profile_picture'], 'profile_pictures');
                $data['profile_picture'] = $path['path'];
            }
            $data['verification_token'] = Str::random(60);
            $newUser = User::create($data);
            $newUser->token = $newUser->createToken('Personal Access Token')->accessToken;
            if (isset($data['interests'])) {
                $newUser->interests()->sync($data['interests']);
            }
            $newUser->notify(new VerifyEmailNotification($newUser->verification_token));  //treiggers verification email
            DB::commit();
            return $newUser->load('interests');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }
}
