<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'start_datetime'   => $this->start_datetime ? $this->start_datetime->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s') : null,
            'end_datetime'     => $this->end_datetime ? $this->end_datetime->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s') : null,
            'total_seat'       => $this->total_seat,
            'remaining_seat'   => $this->remaining_seat,
            'status'           => $this->status(),
            'view_count'       => $this->view_count,
            'is_registered'    => $request->user('api') ? $this->registrations()->where('user_id', $request->user('api')->id)->exists() : false,
            'seat_price'       => $this->seat_price,
            'map_address'      => $this->map_address,
            'map_url'          => $this->map_url,
            'city'             => $this->city,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'cover_image'      => $this->cover_image ? url('api/storage/' . $this->cover_image) : null,
            'tags'             => $this->tags,
            'categories'       => CategoryResource::collection($this->whenLoaded('categories')),
            'organizer'        => $this->whenLoaded('organizer', function () {
                return $this->organizer ? [
                    'name' => $this->organizer->name,
                    'email' => $this->organizer->email,
                    'phone' => $this->organizer->phone_no,
                    'profile_picture' => $this->organizer->profile_picture ? url('api/storage/' . $this->organizer->profile_picture) : null,
                ] : null;
            }),
            'event_images'     => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return url('api/storage/' . $image->image);
                });
            }),
            'feedbacks'        => $this->whenLoaded('feedbacks', function () {
                return $this->feedbacks->map(function ($feedback) {
                    return [
                        'user' => $feedback->user ? $feedback->user->name : 'Unknown',
                        'comment' => $feedback->comment,
                    ];
                });
            }),
        ];
    }
}
