<?php

namespace App\Services;

use App\Enums\EventCancellationReasonEnum;
use App\Enums\EventRegistartionStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Event;
use App\Models\EventRegistration;
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

            $existingRegistration = EventRegistration::where(['user_id' => $user->id, 'event_id' => $event->id, 'status' => EventRegistartionStatusEnum::REGISTERED])->first();
            if ($existingRegistration) return 'You have already registered for this event.';

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

    public function cancel($eventRegistration, $data)
    {
        DB::beginTransaction();
        try {
            $event = $eventRegistration->event;
            $user = Auth::user();
            $now = now();

            if (!$event) return "Event not found";

            if ($eventRegistration->user_id !== $user->id) return 'You can not cancel this registration.';

            if ($event->status === 'cancelled') return 'Cannot cancel registration because the event itself is cancelled.';

            if ($eventRegistration->status == EventRegistartionStatusEnum::CANCELLED->value || $eventRegistration->cancelled_at) return 'You have already cancelled this registration';

            if ($now->gte($event->start_datetime) || $now->gte($event->end_datetime)) {
                return 'Cancellation not allowed. Event has already started or completed.';
            }

            if ($event->start_datetime->subDay()->lte(now())) return 'Cannot cancel registration less than 24 hours before the event starts.';

            $data['cancelled_at'] = now();
            $data['status'] = EventRegistartionStatusEnum::CANCELLED->value;
            $eventRegistration->update($data);
            $event->update(['remaining_seat' => $event->remaining_seat + $eventRegistration->seats_booked]);
            DB::commit();
            return true;
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return $exception->getMessage();
        }
    }
}
