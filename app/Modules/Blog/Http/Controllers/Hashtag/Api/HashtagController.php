<?php

namespace App\Modules\Blog\Http\Controllers\Hashtag\Api;


use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Blog\BlogUseCase;
use App\Modules\Blog\Application\UseCases\Hashtag\HashtagUseCase;
use App\Modules\Blog\Http\Requests\Blog\Dashboard\FetchBlogRequest;
use App\Modules\Blog\Http\Requests\Hashtag\Api\FetchHashtagRequest;

class HashtagController extends Controller
{
    protected $hashtagUseCase;

    public function __construct(HashtagUseCase $hashtagUseCase)
    {
        $this->hashtagUseCase = $hashtagUseCase;
    }


    public function fetchHashtags(FetchHashtagRequest $request)
    {
        return $this->hashtagUseCase->fetchHashtags($request->toDTO())->response();
    }

    public function fetchLatestHashtags(FetchHashtagRequest $request)
    {
        return $this->hashtagUseCase->fetchLatestHashtags($request->toDTO())->response();
    }
}
