<?php

namespace App\Modules\Blog\Http\Controllers\Hashtag\Dashboard;


use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\UseCases\Hashtag\HashtagUseCase;
use App\Modules\Blog\Http\Requests\Hashtag\Global\HashtagIdRequest;
use App\Modules\Blog\Http\Requests\Hashtag\Dashboard\FetchHashtagRequest;
use App\Modules\Blog\Http\Requests\Hashtag\Dashboard\CreateHashtagRequest;
use App\Modules\Blog\Http\Requests\Hashtag\Dashboard\UpdateHashtagRequest;

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

    public function fetchHashtagDetails(HashtagIdRequest $request)
    {
        return $this->hashtagUseCase->fetchHashtagDetails($request->toDTO())->response();
    }


    public function createHashtag(CreateHashtagRequest $request)
    {
        return $this->hashtagUseCase->createHashtag($request->toDTO())->response();
    }

    public function updateHashtag(UpdateHashtagRequest $request)
    {
        return $this->hashtagUseCase->updateHashtag($request->toDTO())->response();
    }

    public function deleteHashtag(HashtagIdRequest $request)
    {
        return $this->hashtagUseCase->deleteHashtag($request->toDTO())->response();
    }
}
