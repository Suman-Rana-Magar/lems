<?php

namespace App\Services;

use App\Helper;
use App\Http\Resources\EventIndexResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Slug;
use App\Upload;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventCancelled;

class EventService
{
    use Helper, Upload, Slug;

    public function index($request)
    {
        // Recommended event(s)
        $recommended = EventRecommendationService::getRecommendedEvents();
        $recommendedIds = is_object($recommended) ? $recommended->pluck('id') : [];

        // Paginated list excluding recommended
        $otherQuery = Event::whereNotIn('id', $recommendedIds)
            ->where('status', '!=', 'cancelled');
        $other = $this->paginateRequest($request, $otherQuery, EventIndexResource::class, '*', 'categories');

        return [
            'recommended' => is_object($recommended) ? EventIndexResource::collection($recommended) : [],
            'other' => $other,
        ];
    }

    public function showBySlug($slug)
    {
        $event = Event::where('slug', $slug)->first();
        if (!$event) return 'Event not found';

        $event->increment('view_count');

        return $event->load(['categories', 'organizer', 'images', 'feedbacks.user']);
    }

    public function getEventsByCategory($slug)
    {
        $category = \App\Models\Category::where('slug', $slug)->first();
        if (!$category) return 'Category not found';

        return Event::byCategory($slug)
            ->where('status', '!=', 'cancelled')
            ->with(['categories', 'organizer', 'images'])
            ->latest()
            ->paginate(12);
    }

    public function getEventsByStatus($status)
    {
        return Event::byStatus($status)
            ->with(['categories', 'organizer', 'images'])
            ->latest()
            ->paginate(12);
    }

    public function getEventsByDateRange($startDate, $endDate = null)
    {
        // If no end date, assume single day or open ended?
        // Let's assume the route handles the "range" parsing or we accept a generic "date" string
        // For now, let's implement strict range.
        if (!$endDate) $endDate = $startDate;

        return Event::byDateRange($startDate, $endDate)
            ->where('status', '!=', 'cancelled')
            ->with(['categories', 'organizer', 'images'])
            ->latest()
            ->paginate(12);
    }

    public function getEventsByPrice($min, $max)
    {
        return Event::byPriceRange($min, $max)
            ->where('status', '!=', 'cancelled')
            ->with(['categories', 'organizer', 'images'])
            ->latest()
            ->paginate(12);
    }

    public function getNearbyEvents($lat, $lng, $radius = 10)
    {
        return Event::nearMe($lat, $lng, $radius)
            ->where('status', '!=', 'cancelled')
            ->with(['categories', 'organizer', 'images'])
            ->paginate(12);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Subtract 05:45 from datetime fields
            $data['start_datetime'] = Carbon::parse($data['start_datetime'])->subHours(5)->subMinutes(45)->format('Y-m-d H:i:s');
            $data['end_datetime'] = Carbon::parse($data['end_datetime'])->subHours(5)->subMinutes(45)->format('Y-m-d H:i:s');

            $data['status'] = $this->getEventStatus($data['start_datetime'], $data['end_datetime']);

            //check for overlapping events
            if ($this->checkOverlappingEvent($data['start_datetime'], $data['end_datetime']))
                return 'You already have an event scheduled during this time.';
            $eventLocation = $this->getEventLocationInfo($data['latitude'], $data['longitude']);
            $data = array_merge($data, $eventLocation);
            $data['municipality_id'] = $this->getMunicipalityIdByName($data['city']);
            $data['remaining_seat'] = $data['total_seat'];
            $data['slug'] = $this->generateSlug($data['title'], Event::class);
            if (isset($data['cover_image']) && is_file($data['cover_image'])) {
                $path = $this->UploadFile($data['cover_image'], 'event_cover_images');
                $data['cover_image'] = $path['path'];
            }

            $event = $user->events()->create($data);
            if (isset($data['categories'])) {
                $event->categories()->sync($data['categories']);
            }
            DB::commit();
            return $event->load('categories');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }

    public function update($event, array $data)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            if ($user->id != $event->organizer_id) return "Sorry, you can not edit this event";

            // Subtract 05:45 from datetime fields
            $data['start_datetime'] = Carbon::parse($data['start_datetime'])->subHours(5)->subMinutes(45)->format('Y-m-d H:i:s');
            $data['end_datetime'] = Carbon::parse($data['end_datetime'])->subHours(5)->subMinutes(45)->format('Y-m-d H:i:s');

            $data['status'] = $this->getEventStatus($data['start_datetime'], $data['end_datetime']);

            //check for overlapping events
            if ($this->checkOverlappingEvent($data['start_datetime'], $data['end_datetime'], $event))
                return 'You already have an event scheduled during this time.';
            if (!isset($data['cover_image'])) unset($data['cover_image']);
            $eventLocation = $this->getEventLocationInfo($data['latitude'], $data['longitude']);
            $data = array_merge($data, $eventLocation);
            $data['municipality_id'] = $this->getMunicipalityIdByName($data['city']);
            if (isset($data['cover_image']) && is_file($data['cover_image'])) {
                $previousCoverImage = $event->cover_image;
                if ($previousCoverImage) $this->deleteFile($previousCoverImage);
                $path = $this->UploadFile($data['cover_image'], 'event_cover_images');
                $data['cover_image'] = $path['path'];
            }

            $event->update($data);
            if (isset($data['categories'])) {
                $event->categories()->sync($data['categories']);
            }
            DB::commit();
            return $event->load('categories');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }

    public function cancel($event)
    {
        if ($event->organizer_id !== Auth::id()) return "Sorry, you can not cancel this event";

        // Check if event is already completed
        if (Carbon::parse($event->end_datetime)->isPast()) return "Cannot cancel a completed event.";

        // Check if event is already cancelled
        if ($event->status === 'cancelled') return "Event is already cancelled.";

        // Cancel the event
        $event->status = 'cancelled';
        $event->save();

        // Notify registered users (placeholder)
        // Notify registered users
        foreach ($event->registrations as $registration) {
            if ($registration->user) {
                Mail::to($registration->user)->send(new EventCancelled($event, $registration->user));
            }
        }

        return true;
    }

    public function uploadImages($eventId, array $images)
    {
        $event = Event::findOrFail($eventId);

        if ($event->organizer_id !== Auth::id()) {
            return "Unauthorized";
        }

        if ($event->status() !== 'completed') {
            return "Event must be completed to upload images.";
        }

        $currentCount = $event->images()->count();
        $newCount = count($images);

        if ($currentCount + $newCount > 5) {
            return "You can only upload a maximum of 5 images.";
        }

        $uploadedImages = [];
        foreach ($images as $image) {
            if (is_file($image)) {
                $path = $this->UploadFile($image, 'event_images');
                $uploadedImages[] = $event->images()->create(['image' => $path['path']]);
            }
        }

        return $uploadedImages;
    }

    public function storeFeedback($eventId, array $data)
    {
        $event = Event::findOrFail($eventId);

        if ($event->status() !== 'completed') {
            return "Event must be completed to give feedback.";
        }

        $userId = Auth::id();

        $isRegistered = $event->registrations()->where('user_id', $userId)->exists();

        if (!$isRegistered) {
            return "Only registered users can give feedback.";
        }

        if ($event->feedbacks()->where('user_id', $userId)->exists()) {
            return "You have already provided feedback for this event.";
        }

        return $event->feedbacks()->create([
            'user_id' => $userId,
            'comment' => $data['comment']
        ]);
    }

    private function checkOverlappingEvent(string $startDatetime, string $endDatetime, $event = null): bool
    {
        $organizerId = Auth::id();

        return Event::where('organizer_id', $organizerId)
            ->when($event, function ($query) use ($event) {
                // Exclude the current event during update
                $query->where('id', '!=', $event->id);
            })
            ->where(function ($query) use ($startDatetime, $endDatetime) {
                $query->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
                    ->orWhere(function ($q) use ($startDatetime, $endDatetime) {
                        $q->where('start_datetime', '<=', $startDatetime)
                            ->where('end_datetime', '>=', $endDatetime);
                    });
            })
            ->exists();
    }

    private function getEventStatus(string $start, string $end): string
    {
        $now = Carbon::now();

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if ($now->lt($startDate)) {
            return 'upcoming';
        } elseif ($now->between($startDate, $endDate)) {
            return 'ongoing';
        } elseif ($now->gt($endDate)) {
            return 'completed';
        }

        return 'cancelled'; // optional fallback
    }
}
