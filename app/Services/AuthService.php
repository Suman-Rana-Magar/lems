<?php

use App\Models\User;

class AuthService
{
    public function login(array $data)
    {
        $user = User::whereEmail($data['email'])->first();
        if (!$user)
            return "Wrong Credential Entered";
        return $user;
    }
}
