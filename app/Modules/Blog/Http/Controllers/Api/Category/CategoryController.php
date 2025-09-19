<?php

namespace App\Modules\Blog\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Category\CategoryUseCase;
use App\Modules\Blog\Http\Requests\Blog\BlogIdRequest;
use App\Modules\Blog\Http\Requests\Blog\FetchBlogRequest;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Blog\Http\Requests\Category\CategoryIdRequest;
use App\Modules\Blog\Http\Requests\Category\FetchCategoryRequest;

class CategoryController extends Controller
{
    protected $categoryUseCase;

    public function __construct(CategoryUseCase $categoryUseCase)
    {
        $this->categoryUseCase = $categoryUseCase;
    }

    /**
     * Fetch a list of blogs with optional filters
     *
     * @param FetchBlogRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCategories(FetchCategoryRequest $request)
    {
        return $this->categoryUseCase->fetchCategories(
            $request->toDTO(),
            ViewTypeEnum::MOBILE->value
        )->response();
    }

    /**
     * Fetch details of a specific blog
     *
     * @param BlogIdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCategoryDetails(CategoryIdRequest $request)
    {
        return $this->categoryUseCase->fetchCategoryDetails(
            $request->toDTO(),
            ViewTypeEnum::MOBILE->value
        )->response();
    }
}
