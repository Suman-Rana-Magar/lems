<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Slug
{
    function generateSlug(string $value, string $modelClass,  string $column = 'slug'): string
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $count = 1;

        // Get table from the model class
        $table = (new $modelClass)->getTable();

        while (DB::table($table)->where($column, $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
