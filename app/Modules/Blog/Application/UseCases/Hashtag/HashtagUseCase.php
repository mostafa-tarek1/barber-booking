<?php

namespace App\Modules\Blog\Application\UseCases\Hashtag;


use App\Modules\Blog\Application\DTOS\Hashtag\HashtagDTO;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Blog\Http\Resources\Hashtag\HashtagResource;
use App\Modules\Blog\Application\DTOS\Hashtag\HashtagFilterDTO;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Hashtag\HashtagRepository;



class HashtagUseCase
{

    protected $hashtagRepository;


    public function __construct(HashtagRepository $hashtagRepository)
    {
        $this->hashtagRepository = $hashtagRepository;
    }

    public function fetchHashtags(HashtagFilterDTO $hashtagFilterDTO): DataStatus
    {
        try {
            $hashtags = $this->hashtagRepository->filter(
                $hashtagFilterDTO,
                operator: 'like',
                translatableFields: ['title'],
                paginate: $hashtagFilterDTO->paginate,
                limit: $hashtagFilterDTO->limit
            );
            $resource = $hashtagFilterDTO->paginate ? HashtagResource::collection($hashtags)->response()->getData(true) : HashtagResource::collection($hashtags);
            return DataSuccess(
                status: true,
                message: 'Hashtag fetched successfully',
                data: $resource,
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
    public function fetchLatestHashtags(HashtagFilterDTO $hashtagFilterDTO): DataStatus
    {
        try {
            $hashtags = $this->hashtagRepository->filter(
                $hashtagFilterDTO,
                operator: 'like',
                translatableFields: ['title'],
                paginate: $hashtagFilterDTO->paginate,
                limit: $hashtagFilterDTO->limit
            )->sortByDesc('created_at')->take($hashtagFilterDTO->limit);
            $resource = $hashtagFilterDTO->paginate ? HashtagResource::collection($hashtags)->response()->getData(true) : HashtagResource::collection($hashtags);
            return DataSuccess(
                status: true,
                message: 'Hashtag fetched successfully',
                data: $resource,
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchHashtagDetails(HashtagFilterDTO $hashtagFilterDTO): DataStatus
    {
        try {
            $hashtag = $this->hashtagRepository->getById($hashtagFilterDTO->hashtag_id);
            return DataSuccess(
                status: true,
                message: 'Course Hashtag fetched successfully',
                data: new HashtagResource($hashtag)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function createHashtag(HashtagDTO $hashtagDTO): DataStatus
    {
        try {
            $hashtag = $this->hashtagRepository->create($hashtagDTO);

            return DataSuccess(
                status: true,
                message: ' Hashtag created successfully',
                data: new HashtagResource($hashtag)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function updateHashtag(HashtagDTO $hashtagDTO): DataStatus
    {
        try {
            $hashtag = $this->hashtagRepository->update($hashtagDTO->hashtag_id, $hashtagDTO);

            return DataSuccess(
                status: true,
                message: ' Hashtag updated successfully',
                data: new HashtagResource($hashtag)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteHashtag(HashtagFilterDTO $hashtagFilterDTO): DataStatus
    {
        try {
            $hashtag = $this->hashtagRepository->delete($hashtagFilterDTO->hashtag_id);
            return DataSuccess(
                status: true,
                message: ' Hashtag deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
}
