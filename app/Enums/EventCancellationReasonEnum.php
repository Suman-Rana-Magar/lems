<?php

namespace App\Enums;

enum EventCancellationReasonEnum: string
{
    case CHANGE_OF_PLANS = 'change_of_plans';
    case HEALTH_ISSUES = 'health_issues';
    case SCHEDULE_CONFLICT = 'schedule_conflict';
    case EVENT_POSTPONED = 'event_postponed';
    case TRANSPORTATION_ISSUE = 'transportation_issue';
    case OTHER = 'other';
}
