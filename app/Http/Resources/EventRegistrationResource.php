<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seats_booked' => $this->seats_booked,
            'registered_at' => $this->registered_at ? $this->registered_at->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s') : null,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'event' => [
                'id' => $this->event->id,
                'title' => $this->event->title,
                'slug' => $this->event->slug,
                'start_datetime' => $this->event->start_datetime ? $this->event->start_datetime->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s') : null,
                'end_datetime' => $this->event->end_datetime ? $this->event->end_datetime->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s') : null,
                'map_address' => $this->event->map_address,
                'map_url' => $this->event->map_url,
                'status' => $this->event->status,
                'cover_image' => $this->event->cover_image ? url('api/storage/' . $this->event->cover_image) : null,
            ],
            'cancelled_at' => $this->when($this->cancelled_at, function () {
                return $this->cancelled_at->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s');
            }),
            'cancellation_reason' => $this->whenNotNull($this->cancellation_reason),
            'cancellation_note' => $this->whenNotNull($this->cancellation_note),
            'is_ticket_generated' => $this->is_ticket_generated,
        ];
    }
}
