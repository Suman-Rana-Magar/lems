<?php

namespace App\Http\Requests;

use Event;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $event = $this->route('event');

        // Check if event has registrations
        $hasRegistrations = $event && $event->registrations()->count() > 0;

        // If registrations exist, restrict critical fields
        $rules = [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'start_datetime' => $hasRegistrations
                ? ['prohibited'] // cannot edit start if people registered
                : ['sometimes', 'date'],
            'end_datetime' => $hasRegistrations
                ? ['prohibited'] // cannot edit end if people registered
                : ['sometimes', 'date', 'after:start_datetime'],
            'total_seat' => $hasRegistrations
                ? ['sometimes', 'integer', 'min:1'] // optionally allow seat change
                : ['sometimes', 'integer', 'min:1'],
            'seat_price' => $hasRegistrations
                ? ['prohibited'] // cannot edit price if registrations exist
                : ['sometimes', 'numeric', 'min:0'],
            'street' => ['nullable', 'string', 'max:255'],
            'venue' => $hasRegistrations
                ? ['prohibited'] // cannot change venue after registrations
                : ['nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'start_datetime.prohibited' => 'You cannot change the start date because registrations already exist.',
            'end_datetime.prohibited' => 'You cannot change the end date because registrations already exist.',
            'seat_price.prohibited' => 'You cannot change the price because registrations already exist.',
            'venue.prohibited' => 'You cannot change the venue because registrations already exist.',
        ];
    }
}
