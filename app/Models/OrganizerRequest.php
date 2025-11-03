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
        'reason',
        'additional_information',
        'requested_at',
        'approved_at',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
