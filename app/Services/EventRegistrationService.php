<?php

namespace App\Services;

use App\Enums\EventRegistartionStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Event;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventRegistrationService
{
    public function store($data)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $event = Event::findOrFail($data['event_id']);

            if ($event->organizer_id === $user->id) {
                return 'Organizers cannot register for their own events.';
            }

            if ($event->status === 'cancelled') {
                return 'Registration not allowed. Event is cancelled.';
            }

            $now = now();
            if ($now->gte($event->start_datetime) || $now->gte($event->end_datetime)) {
                return 'Registration not allowed. Event has already started or completed.';
            }
            if ($user->registrations()->where('event_id', $event->id)->first()) return 'You have already registered for this event.';

            $data['status'] = EventRegistartionStatusEnum::REGISTERED->value;
            $data['payment_method'] = PaymentMethodEnum::CASH->value;
            $data['payment_status'] = PaymentStatusEnum::PAID->value;
            $registration = $user->registrations()->create($data);
            $remainingSeat = $registration->event->remaining_seat - $data['seats_booked'];
            $registration->event()->update(['remaining_seat' => $remainingSeat]);
            DB::commit();
            return $registration->load('event');
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return $exception->getMessage();
        }
    }
}
