<?php

namespace App\Services;

use App\Helper;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
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
}
