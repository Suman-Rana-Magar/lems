<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizerRequest extends FormRequest
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
            'phone_no' => ['required', 'regex:/^9[78]\d{8}$/'],
            'reason' => ['required', 'string'],
            'additional_information' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'phone_no.regex' => 'The phone number must be a valid 10-digit Nepali mobile number.',
        ];
    }
}
