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
}
