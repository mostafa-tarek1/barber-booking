<?php

namespace App\Modules\Blog\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\DTOS\Blog\BlogFilterDTO;
use App\Modules\Blog\Application\UseCases\Blog\BlogUseCase;
use App\Modules\Blog\Http\Requests\Blog\BlogIdRequest;
use App\Modules\Blog\Http\Requests\Blog\FetchBlogRequest;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;

class BlogController extends Controller
{
    protected $blogUseCase;

    public function __construct(BlogUseCase $blogUseCase)
    {
        $this->blogUseCase = $blogUseCase;
    }

    /**
     * Fetch a list of blogs with optional filters
     *
     * @param FetchBlogRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBlogs(FetchBlogRequest $request)
    {
        return $this->blogUseCase->fetchBlogs(
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
    public function fetchBlogDetails(BlogIdRequest $request)
    {
        return $this->blogUseCase->fetchBlogDetails(
            $request->toDTO(),
            ViewTypeEnum::MOBILE->value
        )->response();
    }
}
