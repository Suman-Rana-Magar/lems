<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EventRecommendationService
{
    public static function getRecommendedEvents()
    {
        $user = Auth::guard('api')->user();
        if (!($user instanceof User)) return null;
        return (self::isNewUser($user) === true) ? self::getRecommendedEventsForNewUser($user) : self::getRecommendedEventsForExperiencedUser($user);
    }

    private static function isNewUser(User $user)
    {
        return $user->registrations()->count() === 0 ? true : false;
    }

    private static function getRecommendedEventsForNewUser(User $user)
    {
        $userInterests = $user->interests()->get()->pluck('id');
        $userMunicipality = $user->municipality_id;
        $now = Carbon::now();

        $events = Event::query()
            // 1) same category as user interests
            ->whereHas('categories', function ($query) use ($userInterests) {
                $query->whereIn('categories.id', $userInterests);
            })
            // 2) exclude cancelled events
            ->where('status', '!=', 'cancelled')
            // 3) only upcoming events (start time after now)
            ->where('start_datetime', '>', $now)
            ->with('categories');

        // 4) prioritize municipality proximity (same first, then nearest by id), then nearest start time
        if ($userMunicipality) {
            $events->orderByRaw('CASE WHEN municipality_id = ? THEN 0 ELSE 1 END', [$userMunicipality])
                ->orderByRaw('ABS(COALESCE(municipality_id, ? + 1000000) - ?) ASC', [$userMunicipality, $userMunicipality]);
        } else {
            // If user municipality unknown, prefer events that have municipality set
            $events->orderByRaw('CASE WHEN municipality_id IS NULL THEN 1 ELSE 0 END');
        }

        return $events->orderBy('start_datetime')
            ->limit(10)
            ->get();
    }

    private static function getRecommendedEventsForExperiencedUser(User $user)
    {
        return [];
    }
}
