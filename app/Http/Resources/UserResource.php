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
            'address' => $this->when($this->relationLoaded('municipality') && $this->municipality, function () {
                $municipality = $this->municipality;
                $district = $municipality->relationLoaded('district') ? $municipality->district : null;
                $province = $district && $district->relationLoaded('province') ? $district->province : null;
                
                return [
                    'province' => $province ? ['id' => $province->id, 'name' => $province->name] : null,
                    'district' => $district ? ['id' => $district->id, 'name' => $district->name] : null,
                    'municipality' => $municipality ? ['id' => $municipality->id, 'name' => $municipality->name] : null,
                ];
            }),
            'ward_no' => $this->ward_no,
            'street' => $this->street,
            'role' => $this->role,
            'is_email_verified' => $this->hasVerifiedEmail() ? true : false,
            'is_phone_verified' => $this->hasVerifiedPhone() ? true : false,
            'interests' => CategoryResource::collection($this->whenLoaded('interests'))
        ];
    }
}
