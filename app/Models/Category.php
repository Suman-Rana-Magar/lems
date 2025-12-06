<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
    ];

    protected $hidden = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_interests', 'category_id', 'user_id')
            ->withTimestamps();
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_categories', 'category_id', 'event_id')
            ->withTimestamps();
    }

    public function categoryRelations()
    {
        return $this->hasMany(CategoryRelation::class, 'category_a_id', 'id');
    }

    public function relatedCategories()
    {
        return $this->categoryRelations()->where('category_a_id', $this->id)->orWhere('category_b_id', $this->id)->get();
    }

    public function relatedCategoriesIds()
    {
        return $this->relatedCategories()->pluck('category_b_id')->concat($this->relatedCategories()->pluck('category_a_id'));
    }
}   
