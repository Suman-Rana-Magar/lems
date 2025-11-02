<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRegisterRequest;
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

    public function register(UserRegisterRequest $request)
    {
        $data = $this->authService->register($request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('User Registered in Successfully !', LoginResource::make($data));
    }

    public function test()
    {
        return $this->successResponse("API working!");
    }
}
