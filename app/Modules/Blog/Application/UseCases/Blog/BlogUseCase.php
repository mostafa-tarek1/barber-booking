<?php

namespace App\Modules\Blog\Application\UseCases\Blog;


use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Blog\Application\DTOS\Blog\BlogDTO;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Blog\Http\Resources\Blog\BlogResource;
use App\Modules\Blog\Application\DTOS\Blog\BlogFilterDTO;
use App\Modules\Blog\Application\DTOS\Hashtag\HashtagDTO;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog\BlogRepository;



class BlogUseCase
{

    protected $blogRepository;
    protected $auth;

    public function __construct(BlogRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
        // dd(AuthGurdTypeEnum::EMPLOYEE->value);
        $this->auth = AuthHolder::getInstance()->getUser(AuthGurdTypeEnum::EMPLOYEE->value);
//        dd($this->auth);

    }

    public function fetchBlogs(BlogFilterDTO $blogFilterDTO): DataStatus
    {
        try {
            // dd($blogFilterDTO->whereHasRelations());
          if (!empty($blogFilterDTO->whereHasRelations())) {
              $blogs = $this->blogRepository->filter(
                  $blogFilterDTO,
                  operator: 'like',
                  translatableFields: ['title'],
                  paginate: $blogFilterDTO->paginate,
                  limit: $blogFilterDTO->limit,
                  whereHasRelations: $blogFilterDTO->whereHasRelations(),
              );
          } else {
              $blogs = $this->blogRepository->filter(
                  $blogFilterDTO,
                  operator: 'like',
                  translatableFields: ['title'],
                  paginate: $blogFilterDTO->paginate,
                  limit: $blogFilterDTO->limit,
              );
          }
            $resource = $blogFilterDTO->paginate ? BlogResource::collection($blogs)->response()->getData(true) : BlogResource::collection($blogs);
            return DataSuccess(
                status: true,
                message: 'Blog fetched successfully',
                data: $resource,
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
    public function fetchLatestBlogs(BlogFilterDTO $blogFilterDTO): DataStatus
    {
        try {
            $blogs = $this->blogRepository->filter(
                $blogFilterDTO,
                operator: 'like',
                translatableFields: ['title'],
                paginate: $blogFilterDTO->paginate,
                limit: $blogFilterDTO->limit,

            )->sortByDesc('created_at')->take($blogFilterDTO->limit);
            $resource = $blogFilterDTO->paginate ? BlogResource::collection($blogs)->response()->getData(true) : BlogResource::collection($blogs);
            return DataSuccess(
                status: true,
                message: 'Blog fetched successfully',
                data: $resource,
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchBlogDetails(BlogFilterDTO $blogFilterDTO): DataStatus
    {
        try {
            if ($blogFilterDTO->blog_id) {
                $blog = $this->blogRepository->getWhere('id', $blogFilterDTO->blog_id, 'first');
            } elseif ($blogFilterDTO->slug) {
                $blog = $this->blogRepository->getWhere('slug', $blogFilterDTO->slug, 'first');
            }
            return DataSuccess(
                status: true,
                message: 'Course Blog fetched successfully',
                data: new BlogResource($blog)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }



    public function createBlog(BlogDTO $blogDTO): DataStatus
    {
        try {
            $blog = $this->blogRepository->create($blogDTO);

            $hashtag = HashtagDTO::fromArray($blogDTO->hashtags);

            if ($blogDTO->hashtags && count($blogDTO->hashtags) > 0) {
                $blog->hashtags()->attach($blogDTO->hashtags);
            }
            if ($blogDTO->categories && count($blogDTO->categories) > 0)  {
                $blog->categories()->attach($blogDTO->categories);
            }
            return DataSuccess(
                status: true,
                message: ' Blog created successfully',
                data: new BlogResource($blog)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function updateBlog(BlogDTO $blogDTO): DataStatus
    {
        try {
            $blog = $this->blogRepository->update($blogDTO->blog_id, $blogDTO);
            if ($blogDTO->hashtags) {
                $blog->hashtags()->sync($blogDTO->hashtags);
            }
            if ($blogDTO->categories) {
                $blog->categories()->sync($blogDTO->categories);
            }
            return DataSuccess(
                status: true,
                message: ' Blog updated successfully',
                data: new BlogResource($blog)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteBlog(BlogFilterDTO $blogFilterDTO): DataStatus
    {
        try {
            $blog = $this->blogRepository->delete($blogFilterDTO->blog_id);
            return DataSuccess(
                status: true,
                message: ' Blog deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
}
