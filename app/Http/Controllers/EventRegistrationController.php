<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRegistrationRequest;
use App\Http\Resources\EventRegistrationResource;
use App\Services\EventRegistrationService;
use Illuminate\Http\Request;

class EventRegistrationController extends BaseController
{
    private $eventRegistrationService;

    public function __construct(EventRegistrationService $eventRegistrationService)
    {
        $this->eventRegistrationService = $eventRegistrationService;
    }

    public function store(StoreEventRegistrationRequest $request)
    {
        $data = $this->eventRegistrationService->store($request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Event registered successful!', EventRegistrationResource::make($data));
    }
}
