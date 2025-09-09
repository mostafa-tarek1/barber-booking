<?php

namespace App\Modules\Blog\Http\Controllers\Blog\Dashboard;

use Illuminate\Http\Request;
use GuzzleHttp\Promise\Create;
use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Blog\BlogUseCase;
use App\Modules\Blog\Http\Requests\Blog\Global\BlogIdRequest;
use App\Modules\Blog\Http\Requests\Blog\Dashboard\FetchBlogRequest;
use App\Modules\Blog\Http\Requests\Blog\Dashboard\CreateBlogRequest;
use App\Modules\Blog\Http\Requests\Blog\Dashboard\UpdateBlogRequest;

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

    public function fetchBlogDetails(BlogIdRequest $request)
    {
        return $this->blogUseCase->fetchBlogDetails($request->toDTO())->response();
    }


    public function createBlog(CreateBlogRequest $request)
    {
        return $this->blogUseCase->createBlog($request->toDTO())->response();
    }

    public function updateBlog(UpdateBlogRequest $request)
    {
        return $this->blogUseCase->updateBlog($request->toDTO())->response();
    }

    public function deleteBlog(BlogIdRequest $request)
    {
        return $this->blogUseCase->deleteBlog($request->toDTO())->response();
    }
}
