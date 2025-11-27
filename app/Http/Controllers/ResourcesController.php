<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Province;
use Illuminate\Http\Request;

class ResourcesController extends BaseController
{
    public function address()
    {
        $provinces = Province::with('districts.municipalities')->get();

        $data = $provinces->map(function ($province) {
            return [
                'id' => $province->id,
                'name' => $province->name,
                'districts' => $province->districts->map(function ($district) {
                    return [
                        'id' => $district->id,
                        'name' => $district->name,
                        'municipalities' => $district->municipalities->map(function ($municipality) {
                            return [
                                'id' => $municipality->id,
                                'name' => $municipality->name,
                                'no_of_ward' => $municipality->no_of_wards,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return $this->successResponse("Address Retireved Successfully", $data);
    }
    public function categories()
    {
        $categories = Category::all();
        return $this->successResponse("Categories Retireved Successfully", CategoryResource::collection($categories));
    }

    public function enums()
    {
        return $this->successResponse("Enums Retireved Successfully", [
            'roles' => \App\Enums\RoleEnum::cases(),
            'event_status' => \App\Enums\EventEnum::cases(),
            'event_cancellation_reasons' => \App\Enums\EventCancellationReasonEnum::cases(),
            'event_registration_status' => \App\Enums\EventRegistartionStatusEnum::cases(),
            'organizer_request_status' => \App\Enums\OrganizerRequestEnum::cases(),
            'payment_methods' => \App\Enums\PaymentMethodEnum::cases(),
            'payment_status' => \App\Enums\PaymentStatusEnum::cases(),
        ]);
    }
}
