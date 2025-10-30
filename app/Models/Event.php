<?php

namespace App\Models;

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
        'max_participants',
        'status',
        'view_count',
        'seat_price',
    ];

    protected $hidden = [];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'max_participants' => 'integer',
        'view_count' => 'integer',
        'seat_price' => 'decimal:2',
    ];
}
