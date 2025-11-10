<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerRequestResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? $this->name,
                'email' => $this->user->email ?? null,
                'phone' => $this->user->phone_no ?? null,
            ],
            'name' => $this->name,
            'reason' => $this->reason,
            'additional_information' => $this->additional_information,
            'status' => $this->status,
            'requested_at' => $this->requested_at,
            'approved_at' => $this->whenNotNull($this->approved_at),
            'rejection_reason' => $this->whenNotNull($this->rejection_reason),
        ];
    }
}
