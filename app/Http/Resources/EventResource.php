<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'description'      => $this->description,
            // 'organizer_id'     => $this->organizer_id,
            // 'municipality_id'  => $this->municipality_id,
            'start_datetime'   => $this->start_datetime,
            'end_datetime'     => $this->end_datetime,
            'total_seat'       => $this->total_seat,
            'remaining_seat'   => $this->remaining_seat,
            'status'           => $this->status(),
            'view_count'       => $this->view_count,
            'seat_price'       => $this->seat_price,
            'map_address'      => $this->map_address,
            'map_url'          => $this->map_url,
            'city'             => $this->city,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'cover_image'      => asset('storage/' . $this->cover_image),
            'tags'             => $this->tags,
            'categories'       => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
