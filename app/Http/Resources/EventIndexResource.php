<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            // 'description'      => $this->description,
            // 'organizer_id'     => $this->organizer_id,
            // 'municipality_id'  => $this->municipality_id,
            'start_datetime'   => $this->start_datetime ? $this->start_datetime->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s') : null,
            'end_datetime'     => $this->end_datetime ? $this->end_datetime->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s') : null,
            'total_seat'       => $this->total_seat,
            'remaining_seat'   => $this->remaining_seat,
            'status'           => $this->status(),
            // 'view_count'       => $this->view_count,
            'seat_price'       => $this->seat_price,
            'map_address'      => $this->map_address,
            'map_url'          => $this->map_url,
            'city'             => $this->city,
            // 'latitude'         => $this->latitude,
            // 'longitude'        => $this->longitude,
            'cover_image'      => $this->cover_image ? url('api/storage/' . $this->cover_image) : null,
            // 'tags'             => $this->tags,
            'categories'       => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
