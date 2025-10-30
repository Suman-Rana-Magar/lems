<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;
use AuthService;
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
        return "hi";
        $data = $this->authService->login($request->validated());
        return $this->successResponse('User Logged in Successfully !', LoginResource::make($data));
    }
}
