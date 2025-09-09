<?php

namespace App\Modules\Blog\Http\Controllers\Category\Dashboard;


use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Category\CategoryUseCase;
use App\Modules\Blog\Http\Requests\Category\Global\CategoryIdRequest;
use App\Modules\Blog\Http\Requests\Category\Dashboard\FetchCategoryRequest;
use App\Modules\Blog\Http\Requests\Category\Dashboard\CreateCategoryRequest;
use App\Modules\Blog\Http\Requests\Category\Dashboard\UpdateCategoryRequest;

class CategoryController extends Controller
{
    protected $categoryUseCase;

    public function __construct(CategoryUseCase $categoryUseCase)
    {
        $this->categoryUseCase = $categoryUseCase;
    }


    public function fetchCategories(FetchCategoryRequest $request)
    {
        return $this->categoryUseCase->fetchCategories($request->toDTO())->response();
    }

    public function fetchCategoryDetails(CategoryIdRequest $request)
    {
        return $this->categoryUseCase->fetchCategoryDetails($request->toDTO())->response();
    }


    public function createCategory(CreateCategoryRequest $request)
    {
        return $this->categoryUseCase->createCategory($request->toDTO())->response();
    }

    public function updateCategory(UpdateCategoryRequest $request)
    {
        return $this->categoryUseCase->updateCategory($request->toDTO())->response();
    }

    public function deleteCategory(CategoryIdRequest $request)
    {
        return $this->categoryUseCase->deleteCategory($request->toDTO())->response();
    }
}
