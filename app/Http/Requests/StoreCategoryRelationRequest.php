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
            'relations' => ['required', 'array'],
            'relations.*.category_a' => [
                'required',
                'exists:categories,slug',
            ],
            'relations.*.category_b' => [
                'required',
                'exists:categories,slug',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1]; // get the array index
                    $categoryA = $this->input("relations.$index.category_a");

                    if ($categoryA === $value) {
                        $fail("category_a and category_b cannot be the same.");
                    }

                    // Check for duplicate pairs
                    $allRelations = $this->input('relations', []);
                    foreach ($allRelations as $i => $relation) {
                        if ($i != $index &&
                            (($relation['category_a'] === $categoryA && $relation['category_b'] === $value) ||
                             ($relation['category_a'] === $value && $relation['category_b'] === $categoryA))) {
                            $fail("Duplicate category pair detected: $categoryA → $value.");
                        }
                    }
                }
            ],
            'relations.*.relatedness' => ['required', 'numeric', 'between:0,1'],
        ];
    }
}
