<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(PaginationRequest $request)
    {
        $users = $this->userService->index($request->validated());
        return $this->successResponse('Users retrieved successfully', $users);
    }

    public function show(Request $request)
    {
        $user = $request->user()->load(['interests', 'municipality.district.province']);
        return $this->successResponse('Profile retrieved successfully', UserResource::make($user));
    }

    public function update(UserUpdateRequest $request)
    {
        $user = $this->userService->update($request->user(), $request->validated());
        return !is_object($user) ? $this->errorResponse($user) : $this->successResponse('Profile updated successfully', UserResource::make($user));
    }
}
