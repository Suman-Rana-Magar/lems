<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelEventRegistrationRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreEventRegistrationRequest;
use App\Http\Resources\EventRegistrationResource;
use App\Models\EventRegistration;
use App\Services\EventRegistrationService;
use Illuminate\Http\Request;

class EventRegistrationController extends BaseController
{
    private $eventRegistrationService;

    public function __construct(EventRegistrationService $eventRegistrationService)
    {
        $this->eventRegistrationService = $eventRegistrationService;
    }

    public function index(PaginationRequest $request)
    {
        $data = $this->eventRegistrationService->index($request->validated());
        return $this->successResponse('Event registration list retrieved successfully', $data);
    }

    public function myList(PaginationRequest $request)
    {
        $data = $this->eventRegistrationService->myList($request->validated());
        return $this->successResponse('Event registration list retrieved successfully', $data);
    }

    public function store(StoreEventRegistrationRequest $request)
    {
        $data = $this->eventRegistrationService->store($request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Event registered successful!', EventRegistrationResource::make($data));
    }

    public function cancel(EventRegistration $eventRegistration, CancelEventRegistrationRequest $request)
    {
        $data = $this->eventRegistrationService->cancel($eventRegistration, $request->validated());
        return $data !== true ? $this->errorResponse($data) : $this->successResponse('Event registration cancelled successfully');
    }

    public function show(EventRegistration $eventRegistration)
    {
        $data = $this->eventRegistrationService->show($eventRegistration);
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Event registration retrieved successfully', EventRegistrationResource::make($data));
    }
}
