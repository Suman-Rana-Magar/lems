<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            // 'organizer_id' => ['required', 'exists:users,id'],
            // 'municipality_id' => ['required', 'exists:municipalities,id'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime', 'after:now'],
            'total_seat' => ['required', 'integer', 'min:1'],
            // 'status' => ['nullable', 'in:upcoming,ongoing,completed,cancelled'],
            // 'view_count' => ['nullable', 'integer', 'min:0'],
            'seat_price' => ['required', 'numeric', 'min:0'],
            'street' => ['nullable', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'cover_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // 'slug' => ['nullable', 'string', 'max:255', 'unique:events,slug'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'categories' => ['required', 'array'],
            'categories.*' => ['required', 'integer', 'exists:categories,id']
        ];
    }

    public function messages(): array
    {
        return [
            'cover_image.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
