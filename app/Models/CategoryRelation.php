<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryRelation extends Model
{
    protected $fillable = [
        'category_a_id',
        'category_b_id',
        'relatedness',
    ];

    protected $hidden = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function categoryA()
    {
        return $this->belongsTo(Category::class, 'category_a_id');
    }

    public function categoryB()
    {
        return $this->belongsTo(Category::class, 'category_b_id');
    }
}
