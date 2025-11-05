<?php

namespace App\Http\Resources;

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
            'registered_at' => $this->registered_at,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'event' => [
                'id' => $this->event->id,
                'title' => $this->event->title,
                'slug' => $this->event->slug,
                'start_datetime' => $this->event->start_datetime,
                'end_datetime' => $this->event->end_datetime,
                'map_address' => $this->event->map_address,
                'map_url' => $this->event->map_url,
                'status' => $this->event->status,
                'cover_image' => asset('storage/' . $this->event->cover_image),
            ],
            'cancelled_at' => $this->whenNotNull($this->cancelled_at),
            'cancellation_reason' => $this->whenNotNull($this->cancellation_reason),
            'cancellation_note' => $this->whenNotNull($this->cancellation_note),
            'is_ticket_generated' => $this->is_ticket_generated,
        ];
    }
}
