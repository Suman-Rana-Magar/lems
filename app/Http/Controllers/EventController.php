<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends BaseController
{
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(PaginationRequest $request)
    {
        $data = $this->eventService->index($request->validated());
        return $this->successResponse('Event list retrieved successfully', $data);
    }

    public function showBySlug(string $slug)
    {
        $data = $this->eventService->showBySlug($slug);
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Event retrieved successfully', EventResource::make($data));
    }

    public function store(StoreEventRequest $request)
    {
        $data = $this->eventService->store($request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Event stored successfully', EventResource::make($data));
    }

    public function update(Event $event, UpdateEventRequest $request)
    {
        $data = $this->eventService->update($event, $request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Event updated successfully', EventResource::make($data));
    }

    public function cancel(Event $event)
    {
        $data = $this->eventService->cancel($event);
        return $data !== true ? $this->errorResponse($data) : $this->successResponse('Event cancelled successfully');
    }

    public function uploadImages(Request $request, $eventId)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $this->eventService->uploadImages($eventId, $request->file('images'));

        return is_string($data) ? $this->errorResponse($data) : $this->successResponse('Images uploaded successfully', $data);
    }

    public function storeFeedback(Request $request, $eventId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $data = $this->eventService->storeFeedback($eventId, $request->all());

        return is_string($data) ? $this->errorResponse($data) : $this->successResponse('Feedback submitted successfully', $data);
    }
}
