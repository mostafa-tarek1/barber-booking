<?php

namespace App\Modules\Blog\Http\Controllers\Blog\Api;


use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Blog\BlogUseCase;
use App\Modules\Blog\Http\Requests\Blog\Global\BlogIdRequest;
use App\Modules\Blog\Http\Requests\Blog\Dashboard\FetchBlogRequest;

class BlogController extends Controller
{
    protected $blogUseCase;

    public function __construct(BlogUseCase $blogUseCase)
    {
        $this->blogUseCase = $blogUseCase;
    }


    public function fetchBlogs(FetchBlogRequest $request)
    {
        return $this->blogUseCase->fetchBlogs($request->toDTO())->response();
    }

    public function fetchLatestBlogs(FetchBlogRequest $request)
    {
        return $this->blogUseCase->fetchLatestBlogs($request->toDTO())->response();
    }

    public function fetchBlogDetails(BlogIdRequest $request)
    {
        return $this->blogUseCase->fetchBlogDetails($request->toDTO())->response();
    }
}
