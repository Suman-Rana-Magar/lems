<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone_no' => $this->phone_no,
            'profile_picture' => asset('storage/' . $this->profile_picture),
            'municipality_id' => $this->municipality_id,
            'ward_no' => $this->ward_no,
            'street' => $this->street,
            'role' => $this->role,
            'interests' => CategoryResource::collection($this->whenLoaded('interests'))
        ];
    }
}
