<?php

namespace App\Modules\Blog\Http\Controllers\Api\Hashtag;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Hashtag\HashtagUseCase;
use App\Modules\Blog\Http\Requests\Blog\BlogIdRequest;
use App\Modules\Blog\Http\Requests\Blog\FetchBlogRequest;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Blog\Http\Requests\Hashtag\HashtagIdRequest;
use App\Modules\Blog\Http\Requests\Hashtag\FetchHashtagRequest;

class HashtagController extends Controller
{
    protected $hashtagUseCase;

    public function __construct(HashtagUseCase $hashtagUseCase)
    {
        $this->hashtagUseCase = $hashtagUseCase;
    }

    /**
     * Fetch a list of blogs with optional filters
     *
     * @param FetchBlogRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchHashtags(FetchHashtagRequest $request)
    {
        return $this->hashtagUseCase->fetchHashtags(
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
    public function fetchHashtagDetails(HashtagIdRequest $request)
    {
        return $this->hashtagUseCase->fetchHashtagDetails(
            $request->toDTO(),
            ViewTypeEnum::MOBILE->value
        )->response();
    }
}
