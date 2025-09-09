<?php

namespace App\Modules\Blog\Http\Controllers\Category\Api;


use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Blog\BlogUseCase;
use App\Modules\Blog\Application\UseCases\Category\CategoryUseCase;
use App\Modules\Blog\Http\Requests\Blog\Dashboard\FetchBlogRequest;
use App\Modules\Blog\Http\Requests\Category\Api\FetchCategoryRequest;

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

    public function fetchLatestCategories(FetchCategoryRequest $request)
    {
        return $this->categoryUseCase->fetchLatestCategories($request->toDTO())->response();
    }
}
