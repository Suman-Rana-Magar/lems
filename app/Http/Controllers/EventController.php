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

    public function byCategory(string $slug)
    {
        $data = $this->eventService->getEventsByCategory($slug);
        return is_string($data) ? $this->errorResponse($data) : $this->successResponse('Events retrieved successfully', $data);
    }

    public function byStatus(string $status)
    {
        $data = $this->eventService->getEventsByStatus($status);
        return $this->successResponse('Events retrieved successfully', $data);
    }

    public function byDate(string $range)
    {
        // Format: YYYY-MM-DD,YYYY-MM-DD or YYYY-MM-DD
        $dates = explode(',', $range);
        $start = $dates[0];
        $end = isset($dates[1]) ? $dates[1] : null;

        $data = $this->eventService->getEventsByDateRange($start, $end);
        return $this->successResponse('Events retrieved successfully', $data);
    }

    public function byPrice(string $range)
    {
        // Format: min-max or min,max
        $prices = preg_split('/[-,]/', $range);
        $min = $prices[0] ?? 0;
        $max = $prices[1] ?? 99999999;

        $data = $this->eventService->getEventsByPrice($min, $max);
        return $this->successResponse('Events retrieved successfully', $data);
    }

    public function nearMe(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric'
        ]);

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $radius = $request->input('radius', 10); // default 10km

        $data = $this->eventService->getNearbyEvents($lat, $lng, $radius);
        return $this->successResponse('Events retrieved successfully', $data);
    }
}
