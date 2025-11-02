<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    private $categoryService;
    
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(PaginationRequest $request)
    {
        $data = $this->categoryService->index($request->validated());
        return $this->successResponse('Categories retrieved successfully', $data);
    }

    public function store(CategoryStoreRequest $request)
    {
        $data = $this->categoryService->store($request->validated());
        return !is_array($data) && !is_object($data[0]) ? $this->errorResponse($data) : $this->successResponse('Categories created successfully', CategoryResource::collection($data));
    }

    public function update(Category $category, CategoryUpdateRequest $request)
    {
        $data = $this->categoryService->update($category, $request->validated());
        return !is_object($data) ? $this->errorResponse($data) : $this->successResponse('Category updated successfully', CategoryResource::make($data));
    }

    public function delete(Category $category)
    {
        $data = $this->categoryService->delete($category);
        return !$data ? $this->errorResponse($data) : $this->successResponse('Category deleted successfully');
    }
}
