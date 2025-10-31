<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->orWhere('username', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password))
            return ('Invalid login credentials.');
        $user->token = $user->createToken('Personal Access Token')->accessToken;
        return $user;
    }
}
