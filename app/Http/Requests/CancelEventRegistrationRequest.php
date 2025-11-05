<?php

namespace App\Http\Requests;

use App\Enums\EventCancellationReasonEnum;
use Illuminate\Foundation\Http\FormRequest;

class CancelEventRegistrationRequest extends FormRequest
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
        $reasons = array_column(EventCancellationReasonEnum::cases(), 'value');
        return [
            'cancellation_reason' => ['required', 'in:' . implode(',', $reasons)],
            'cancellation_note'   => ['required_if:cancellation_reason,' . EventCancellationReasonEnum::OTHER->value, 'string', 'max:255'],
        ];
    }
}
