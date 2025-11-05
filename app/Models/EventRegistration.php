<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'seats_booked',
        'registered_at',
        'status',
        'payment_status',
        'cancelled_at',
        'cancellation_reason',
        'cancellation_note',
        'is_ticket_generated',
        'payment_method'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'event_id' => 'integer',
        'seats_booked' => 'integer',
        'registered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_ticket_generated' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
