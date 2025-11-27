<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name',
        'no_of_wards',
    ];

    protected $hidden = [
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    protected $casts = [
        'no_of_wards' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
