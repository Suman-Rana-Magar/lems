<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreOrganizerRequest;
use App\Http\Resources\OrganizerRequestResource;
use App\Models\OrganizerRequest;
use App\Services\OrganizerRequestService;
use Illuminate\Http\Request;

class OrganizerRequestController extends BaseController
{
    private $service;

    public function __construct(OrganizerRequestService $service)
    {
        $this->service = $service;
    }

    public function index(PaginationRequest $request)
    {
        $data = $this->service->index($request->validated());
        return $this->successResponse('Organizer request list retrieved successfully', $data);
    }

    public function store(StoreOrganizerRequest $request)
    {
        $data = $this->service->store($request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Organizer request submitted successfully', OrganizerRequestResource::make($data));
    }

    public function myRequests(PaginationRequest $request)
    {
        $data = $this->service->myRequests($request->validated());
        return $this->successResponse('My organizer requests retrieved successfully', $data);
    }

    public function show(OrganizerRequest $organizerRequest)
    {
        $data = $this->service->show($organizerRequest);
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Organizer Request retrieved successfully', OrganizerRequestResource::make($data));
    }

    public function approve(OrganizerRequest $organizerRequest)
    {
        $data = $this->service->approve($organizerRequest);
        return $data !== true ? $this->errorResponse($data) : $this->successResponse('Organizer request approved successfully.');
    }

    public function reject(OrganizerRequest $organizerRequest, Request $request)
    {
        $data = $this->service->reject($organizerRequest, $request->validate(['rejection_reason' => 'required|string|max:255',]));
        return $data !== true ? $this->errorResponse($data) : $this->successResponse('Organizer request rejected successfully.');
    }
}
