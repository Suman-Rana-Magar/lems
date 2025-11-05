<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodEnum;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRegistrationRequest extends FormRequest
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
        $paymentMethod = array_column(PaymentMethodEnum::cases(), 'value');
        return [
            'event_id' => ['required', 'exists:events,id'],
            'seats_booked' => ['required', 'integer', 'min:1', function ($attribute, $value, $fail) {
                $event = Event::find($this->event_id);
                if ($event) {
                    $maxSeat = $event->remaining_seat < 10 ? $event->remaining_seat : 10;
                    if ($value > $maxSeat) $fail("You can book maximum {$maxSeat} seats");
                }
            }],
            // 'payment_method' => ['required', 'in:' . implode(',', $paymentMethod)]
        ];
    }
}
