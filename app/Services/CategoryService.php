<?php

namespace App\Services;

use App\Models\Category;
use App\Slug;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    use Slug;
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $storedCategories = [];
            foreach ($data['categories'] as $catData) {
                $catData['slug'] = $this->generateSlug($catData['name'], Category::class);
                $category = Category::create($catData);
                $storedCategories[] = $category;
            }
            DB::commit();
            return $storedCategories;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }
}
