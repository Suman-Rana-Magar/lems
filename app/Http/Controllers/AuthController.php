<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('User Logged in Successfully !', LoginResource::make($data));
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->successResponse('User logged out successfully.');
    }
}
