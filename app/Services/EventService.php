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
        return $event->load('categories');
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
        foreach ($event->registrations as $registration) {
            // Example: dispatch notification/email
            // Notification::send($registration->user, new EventCancelledNotification($event));
        }

        return true;
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
