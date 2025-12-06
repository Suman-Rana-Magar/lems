<?php

namespace App\Services;

use App\Helper;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\CategoryRelation;
use App\Slug;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    use Slug, Helper;

    private $select = ['id', 'name', 'description', 'slug'];

    public function index(array $request)
    {
        $response = $this->paginateRequest($request, Category::class, CategoryResource::class, $this->select);
        return $response;
    }

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

    public function update($category, array $data)
    {
        DB::beginTransaction();
        try {
            if (!($category->name == $data['name']))
                $data['slug'] = $this->generateSlug($data['name'], Category::class);
            $category->update($data);
            DB::commit();
            return $category;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }

    public function delete($category)
    {
        DB::beginTransaction();
        try {
            $category->delete();
            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }

    public function getRelationPrompt()
    {
        $categories = Category::select('slug')->orderBy('id')->get();

        $categoryCount = count($categories); // e.g., 7
        $expectedPairs = $categoryCount * ($categoryCount - 1) / 2;

        $prompt = "You are a category relation assistant. From the following categories, provide the relatedness score between all pairs of categories from 0.00 to 1.00.

Rules:
1. Include each unique pair once: only category_a_id → category_b_id where category_a_id < category_b_id.
2. Do not include the reverse pair (B->A) or self-relations.
3. You must return exactly $expectedPairs objects — one for each unique pair.
4. Provide ONLY JSON, no extra text or explanations.

The output must strictly follow this JSON format (as an array of objects):

[
    {
        \"category_a\": sports,
        \"category_b\": education,
        \"relatedness\": 0.5
    }
]

Here are the categories: $categories

Provide a relatedness object for every unique pair of categories. The output MUST contain exactly $expectedPairs items.";

        return $prompt;
    }

    public function storeRelation(array $data)
    {
        DB::beginTransaction();
        try {
            if (isset($data['relation']) && count($data['relation']) > 0 && is_array($data['relation'])) {
                $category = new Category();
                foreach ($data['relation'] as $relation) {
                    CategoryRelation::updateOrCreate([
                        'category_a_id' => $category->where('slug', $relation['category_a'])->first()?->id,
                        'category_b_id' => $category->where('slug', $relation['category_b'])->first()?->id,
                    ], [
                        'relatedness' => $relation['relatedness'],
                    ]);
                }
                DB::commit();
                return CategoryRelation::first();
            }
            return "Please provide at least one category relation";
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $exception->getMessage();
        }
    }
}
