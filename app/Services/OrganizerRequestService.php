<?php

namespace App\Services;

use App\Enums\OrganizerRequestEnum;
use App\Enums\RoleEnum;
use App\Helper;
use App\Http\Resources\OrganizerRequestResource;
use App\Models\OrganizerRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizerRequestService
{
    use Helper;
    public function index($request)
    {
        $response = $this->paginateRequest($request, OrganizerRequest::class, OrganizerRequestResource::class, '*', 'user');
        return $response;
    }

    public function show($organizerRequest)
    {
        if (Auth::user()->role !== RoleEnum::ADMIN->value)
            if ($organizerRequest->user_id != Auth::id()) return 'You can not view this request';
        $data = $organizerRequest->load('user');
        return $data;
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if ($user->role !== RoleEnum::NORMAL->value) return 'You can not request this form';
            if ($user->organizerRequests()->where('status', OrganizerRequestEnum::APPROVED->value)->first()) return 'Your request have already approved.';
            if ($user->organizerRequests()->where('status', OrganizerRequestEnum::PENDING->value)->first()) return 'Your request form is on queue. Please try again later.';
            $data['name'] = $user->name;
            $organizerRequest = $user->organizerRequests()->create($data);
            DB::commit();
            return $organizerRequest;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }

    public function approve($organizerRequest)
    {
        if ($organizerRequest->status !== OrganizerRequestEnum::PENDING->value) return 'Request has already been processed.';

        $organizerRequest->status = OrganizerRequestEnum::APPROVED->value;
        $organizerRequest->approved_at = now();
        $organizerRequest->save();
        $organizerRequest->user()->update([
            'role' => RoleEnum::ORGANIZER->value
        ]);

        return true;
    }

    public function reject($organizerRequest, $request)
    {
        if ($organizerRequest->status !== OrganizerRequestEnum::PENDING->value) return 'Request has already been processed.';

        $organizerRequest->status = OrganizerRequestEnum::REJECTED->value;
        $organizerRequest->rejection_reason = $request['rejection_reason'];
        $organizerRequest->save();

        return true;
    }
}
