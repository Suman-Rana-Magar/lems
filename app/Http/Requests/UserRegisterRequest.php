<?php

namespace App\Http\Requests;

use App\Models\Municipality;
use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone_no' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'municipality_id' => ['required', 'exists:municipalities,id'],
            'ward_no' => ['required', 'integer', 'min:1', function ($attribute, $value, $fail) {
                $municipalityId = $this->municipality_id;
                if ($municipalityId) {
                    $municipality = Municipality::find($municipalityId);
                    if (!$municipality || $value > $municipality?->no_of_wards)
                        $fail("The selected ward number must be less than or equal to {$municipality?->no_of_wards} for {$municipality?->name} municipality.");
                }
            }],
            'street' => ['required', 'string', 'max:255'],
            'interests' => ['required', 'array'],
            'interests.*' => ['integer', 'exists:categories,id']
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
