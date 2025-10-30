<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone_no',
        'reason',
        'additional_information',
        'requested_at',
        'approved_at',
        'status',
    ];

    protected $hidden = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
