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
        $response = $this->paginateRequest($request, Event::class, EventIndexResource::class, '*', 'categories');
        return $response;
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
            $user = auth()->user();

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

    private function checkOverlappingEvent(string $startDatetime, string $endDatetime): bool
    {
        $organizerId = Auth::id();

        return Event::where('organizer_id', $organizerId)
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
