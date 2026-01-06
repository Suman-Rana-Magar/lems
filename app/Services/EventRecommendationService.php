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
        $userInterests = $user->interests()->pluck('categories.id')->toArray();
        $query = Event::query()
            ->whereHas('categories', function ($q) use ($userInterests) {
                $q->whereIn('categories.id', $userInterests);
            });

        $events = self::applyStandardFiltersAndSort($query, $user->municipality_id)
            ->limit(10)
            ->get();

        if ($events->count() < 10) {
            $needed = 10 - $events->count();
            $excludeIds = $events->pluck('id')->toArray();
            $popularEvents = self::getPopularEvents($needed, $excludeIds, $user->municipality_id);
            $events = $events->merge($popularEvents);
        }

        return $events;
    }

    private static function getRecommendedEventsForExperiencedUser(User $user)
    {
        // 1. Analyze History
        $registrations = $user->registrations()->with('event.categories')->latest('registered_at')->get();
        if ($registrations->isEmpty()) {
            return self::getRecommendedEventsForNewUser($user);
        }

        $mostRecentCategory = $registrations->first()->event->categories->first()->id ?? null;

        $categoryCounts = [];
        foreach ($registrations as $reg) {
            foreach ($reg->event->categories as $cat) {
                $categoryCounts[$cat->id] = ($categoryCounts[$cat->id] ?? 0) + 1;
            }
        }
        $mostJoinedCategory = array_keys($categoryCounts, max($categoryCounts))[0] ?? null;

        $targetCategoryIds = array_filter(array_unique([$mostRecentCategory, $mostJoinedCategory]));

        if (empty($targetCategoryIds)) {
            return self::getRecommendedEventsForNewUser($user);
        }

        // 2. Primary Query
        $primaryEvents = self::getEventsByCategories($targetCategoryIds, $user->municipality_id);

        if ($primaryEvents->count() >= 10) {
            return $primaryEvents->take(10);
        }

        // 3. Fallback Mechanism (Bidirectional check)
        $relatedCategoryIds = \App\Models\CategoryRelation::where(function ($query) use ($targetCategoryIds) {
            $query->whereIn('category_a_id', $targetCategoryIds)
                ->orWhereIn('category_b_id', $targetCategoryIds);
        })
            ->orderByDesc('relatedness')
            ->get()
            ->map(function ($relation) use ($targetCategoryIds) {
                // Return the ID that is NOT the target (the related one)
                // If both are targets, it doesn't matter which we pick, but ideally we want new ones.
                // However, since we unique() later, picking the other is safe.
                if (in_array($relation->category_a_id, $targetCategoryIds)) {
                    return $relation->category_b_id;
                }
                return $relation->category_a_id;
            })
            ->unique()
            ->values()
            ->toArray();

        $needed = 10 - $primaryEvents->count();
        $relatedEvents = collect();

        if (!empty($relatedCategoryIds)) {
            $excludeIds = $primaryEvents->pluck('id')->toArray();
            $relatedEvents = self::getEventsByCategories($relatedCategoryIds, $user->municipality_id, $excludeIds, $needed);
        }

        $recommended = $primaryEvents->merge($relatedEvents);

        if ($recommended->count() < 10) {
            $needed = 10 - $recommended->count();
            $excludeIds = $recommended->pluck('id')->toArray();
            $popularEvents = self::getPopularEvents($needed, $excludeIds, $user->municipality_id);
            $recommended = $recommended->merge($popularEvents);
        }

        return $recommended;
    }

    private static function getEventsByCategories(array $categoryIds, $municipalityId, array $excludeEventIds = [], $limit = 10)
    {
        $query = Event::query()
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });

        if (!empty($excludeEventIds)) {
            $query->whereNotIn('id', $excludeEventIds);
        }

        return self::applyStandardFiltersAndSort($query, $municipalityId)
            ->limit($limit)
            ->get();
    }

    private static function getPopularEvents($limit, $excludeIds = [], $municipalityId = null)
    {
        $query = Event::query();

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return self::applyStandardFiltersAndSort($query, $municipalityId)
            ->limit($limit)
            ->get();
    }

    private static function applyStandardFiltersAndSort($query, $municipalityId)
    {
        $now = Carbon::now();
        $query->where('status', '!=', 'cancelled')
            ->where('start_datetime', '>', $now)
            ->with('categories');

        if ($municipalityId) {
            $query->orderByRaw('CASE WHEN municipality_id = ? THEN 0 ELSE 1 END', [$municipalityId])
                ->orderByRaw('ABS(COALESCE(municipality_id, ? + 1000000) - ?) ASC', [$municipalityId, $municipalityId]);
        } else {
            $query->orderByRaw('CASE WHEN municipality_id IS NULL THEN 1 ELSE 0 END');
        }

        return $query->orderByDesc('view_count')
            ->orderBy('start_datetime');
    }
}
