<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'organizer_id',
        'municipality_id',
        'start_datetime',
        'end_datetime',
        'total_seat',          // renamed from max_participants
        'remaining_seat',
        'status',
        'view_count',
        'seat_price',
        'map_address',
        'map_url',
        'city',
        'latitude',
        'longitude',
        'cover_image',
        'slug',
        'tags',
    ];

    protected $hidden = [
        // You can hide any internal fields you don't want exposed in API responses
        // Example: 'organizer_id', 'municipality_id'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'total_seat' => 'integer',
        'remaining_seat' => 'integer',
        'view_count' => 'integer',
        'seat_price' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'tags' => 'array',
    ];


    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'event_categories', 'event_id', 'category_id')
            ->withTimestamps();
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function images()
    {
        return $this->hasMany(EventImage::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(EventFeedback::class);
    }

    public function status()
    {
        // If explicitly cancelled in DB, honor that first
        if ($this->getAttribute('status') === 'cancelled') {
            return 'cancelled';
        }

        $timezone = 'Asia/Kathmandu';
        $now = Carbon::now($timezone);
        $start = $this->start_datetime->setTimezone($timezone);
        $end = $this->end_datetime->setTimezone($timezone);

        if ($now->lt($start)) {
            return 'upcoming';
        }

        if ($now->between($start, $end)) {
            return 'ongoing';
        }

        if ($now->gt($end)) {
            return 'completed';
        }

        return $this->getAttribute('status');
    }

    // Scopes
    public function scopeByCategory($query, $slug)
    {
        return $query->whereHas('categories', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    public function scopeByStatus($query, $status)
    {
        // Note: This relies on the DB column 'status' or complex logic.
        // The DB 'status' enum is ['upcoming', 'ongoing', 'completed', 'cancelled'].
        // However, the 'status()' method uses dynamic time checks.
        // For accurate filtering, we must replicate the logical status.

        $timezone = 'Asia/Kathmandu';
        $now = Carbon::now($timezone);

        if ($status === 'cancelled') {
            return $query->where('status', 'cancelled');
        }

        // We only consider non-cancelled events for time-based statuses
        $query->where('status', '!=', 'cancelled');

        if ($status === 'upcoming') {
            return $query->where('start_datetime', '>', $now);
        } elseif ($status === 'ongoing') {
            return $query->where('start_datetime', '<=', $now)
                ->where('end_datetime', '>=', $now);
        } elseif ($status === 'completed') {
            return $query->where('end_datetime', '<', $now);
        }

        return $query;
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_datetime', [$startDate, $endDate]);
    }

    public function scopeByPriceRange($query, $min, $max)
    {
        return $query->whereBetween('seat_price', [$min, $max]);
    }

    public function scopeNearMe($query, $lat, $lng, $radius = 10)
    {
        // Haversine formula
        $haversine = "(6371 * acos(cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(latitude))))";

        return $query->select('*')
            ->selectRaw("{$haversine} AS distance", [$lat, $lng, $lat])
            ->whereRaw("{$haversine} < ?", [$lat, $lng, $lat, $radius])
            ->orderBy('distance');
    }
}
