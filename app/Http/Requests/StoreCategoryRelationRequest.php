<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRelationRequest extends FormRequest
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
            'relation' => ['required', 'array'],
            'relation.*.category_a' => ['required', 'exists:categories,slug'],
            'relation.*.category_b' => ['required', 'exists:categories,slug', function($attribute, $value, $fail) {
                // Extract the index from attribute (e.g., "relation.0.category_b" -> 0)
                preg_match('/relation\.(\d+)\.category_b/', $attribute, $matches);
                $index = $matches[1] ?? null;
                
                if ($index !== null) {
                    $categoryA = $this->input("relation.{$index}.category_a");
                    if ($value == $categoryA) {
                        $fail("category_a and category_b can't be same");
                    }
                }
            }],
            'relation.*.relatedness' => ['required', 'numeric', 'between:0,1'],
        ];
    }
}
