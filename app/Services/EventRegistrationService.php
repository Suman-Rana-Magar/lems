<?php

namespace App\Services;

use App\Enums\EventCancellationReasonEnum;
use App\Enums\EventRegistartionStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\RoleEnum;
use App\Helper;
use App\Http\Resources\EventRegistrationResource;
use App\Models\Event;
use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventRegistrationService
{
    use Helper;

    private $select = ['id', 'user_id', 'event_id', 'seats_booked', 'registered_at', 'status', 'payment_status', 'payment_method', 'cancelled_at', 'cancellation_reason', 'cancellation_note', 'is_ticket_generated'];

    public function index($request)
    {
        $response = $this->paginateRequest($request, EventRegistration::class, EventRegistrationResource::class, $this->select, 'event');
        return $response;
    }

    public function myList($request)
    {
        $user = Auth::user();
        $response = $this->paginateRequest($request, Auth::user()->registrations(), EventRegistrationResource::class, $this->select, 'event');
        return $response;
    }

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

            if ($eventRegistration->is_ticket_generated) return 'Registration can not be cancelled once ticket is generated';

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

    public function show($eventRegistration)
    {
        $user = Auth::user();
        if ($user->role != RoleEnum::ADMIN->value)
            if ($eventRegistration->user_id !== $user->id) return 'You can not view this registration.';
        return $eventRegistration->load('event');
    }

    public function downloadTicket($registration)
    {
        $user = Auth::user();

        // Ensure ownership or admin/organizer
        if ($registration->user_id !== $user->id && !in_array($user->role, [RoleEnum::ADMIN->value, RoleEnum::ORGANIZER->value])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = $registration->event;

        // Generate QR code data URI (handles imagick availability)
        $qrData = json_encode([
            'registration_id' => $registration->id,
            'user_id' => $registration->user->id,
            'event_id' => $event->id,
        ]);
        $qrCodeDataUri = $this->generateQrCodeDataUri($qrData, 120);

        // Mark ticket as generated (prevents cancellation)
        if (!$registration->is_ticket_generated) {
            $registration->update(['is_ticket_generated' => true]);
        }

        $pdf = Pdf::loadView('tickets.event_ticket', [
            'registration' => $registration,
            'event' => $event,
            'user' => $registration->user,
            'qrCodeDataUri' => $qrCodeDataUri,
        ])->setPaper('A4', 'portrait');

        $fileName = 'ticket_' . $event->slug . '_' . $registration->id . '.pdf';

        return $pdf->download($fileName);
    }
}
