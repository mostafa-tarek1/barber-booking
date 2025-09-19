<?php

namespace App\Modules\Blog\Application\UseCases\Blog;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Blog\Application\DTOS\Blog\BlogDTO;
use App\Modules\Blog\Application\DTOS\Blog\BlogHashtagDTO;
use App\Modules\Blog\Http\Resources\Blog\BlogDetailResource;
use App\Modules\Blog\Http\Resources\Blog\BlogResource;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog\BlogHashtagsRepository;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog\BlogRepository;
use App\Modules\Blog\Application\DTOS\Blog\BlogFilterDTO;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Blog\Http\Resources\Api\Blog\BlogResource as ApiBlogResource;
use App\Modules\Blog\Http\Resources\Api\Blog\BlogDetailResource as ApiBlogDetailResource;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Hashtag\HashtagRepository;
use Illuminate\Support\Facades\Log;

class BlogUseCase
{

    protected $blogRepository;
    protected $employee;
    protected $blogHashtagsRepository;
    protected $hashtagRepository;
    public function __construct(BlogRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
        $this->hashtagRepository = new HashtagRepository();
        $this->blogHashtagsRepository = new BlogHashtagsRepository();
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchBlogs(BlogFilterDTO $blogFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($blogFilterDto);
            // build whereHas relations if category_id or hashtag_id provided
            $whereHasRelations = [];
            if (isset($blogFilterDto->category_id) && filled($blogFilterDto->category_id)) {
                // qualify the column to avoid ambiguous `id` when joining
                $whereHasRelations['categories'] = ['categories.id' => $blogFilterDto->category_id];
            }
            if (isset($blogFilterDto->hashtag_id) && filled($blogFilterDto->hashtag_id)) {
                // qualify the column to avoid ambiguous `id` when joining
                $whereHasRelations['hashtags'] = ['hashtags.id' => $blogFilterDto->hashtag_id];
            }

            $blogs = $this->blogRepository->filter(
                $blogFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $blogFilterDto->paginate,
                limit: $blogFilterDto->limit,
                whereHasRelations: $whereHasRelations
            );
            // dd($blogs);
            if (is_null($blogs)) {
                $blogs = collect();
            }
            $resource = $this->HandelBlogResource(
                $blogs,
                $view,
                $blogFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'Blogs fetched successfully',
                data: $blogFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchBlogDetails(BlogFilterDTO $blogFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            if (isset($blogFilterDto->slug)) {
                $blog = $this->blogRepository->getModel()
                    ->where('slug', $blogFilterDto->slug)
                    ->first();

                if (!$blog) {
                    throw new \Exception('Blog not found with the provided slug');
                }
            } else if (isset($blogFilterDto->blog_id)) {
                $blog = $this->blogRepository->getById($blogFilterDto->blog_id);
            } else {
                throw new \Exception('Either blog_id or slug must be provided');
            }

            $resource = $this->HandelBlogDetailResource(
                $blog,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'Blog fetched successfully',
                data: $resource
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
            $blogDTO->created_by = $this->employee->id;
            $blog = $this->blogRepository->create($blogDTO);
            $blog->refresh();
            // dd($blog);
            // hashtags
            if (!empty($blogDTO->hashtag_ids)) {
                $blog->hashtags()->sync($blogDTO->hashtag_ids);
            }

            // categories
            if (!empty($blogDTO->category_ids)) {
                $blog->categories()->sync($blogDTO->category_ids);
            }

            return DataSuccess(
                status: true,
                message: 'Blog created successfully',
                data: new BlogResource($blog->load(['hashtags', 'categories']))
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
            // $blog->refresh();

            // Sync hashtags
            if (!empty($blogDTO->hashtag_ids)) {
                $blog->hashtags()->sync($blogDTO->hashtag_ids);
            } else {
                $blog->hashtags()->detach();
            }

            // Sync categories
            if (!empty($blogDTO->category_ids)) {
                $blog->categories()->sync($blogDTO->category_ids);
            } else {
                $blog->categories()->detach();
            }

            return DataSuccess(
                status: true,
                message: 'Blog updated successfully',
                data: new BlogResource($blog->load(['hashtags', 'categories']))
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function deleteBlog(BlogFilterDTO $blogFilterDto): DataStatus
    {
        try {
            /** @var Blog|null $blog */
            $blog = $this->blogRepository->getById($blogFilterDto->blog_id);

            if (!$blog) {
                return DataFailed(
                    status: false,
                    statusCode: 404,
                    message: 'Blog not found'
                );
            }

            if ($blog->children && $blog->children->count() > 0) {
                return DataFailed(
                    status: false,
                    statusCode: 400,
                    message: 'Blog has children'
                );
            }

            $this->blogRepository->delete($blogFilterDto->blog_id);

            return DataSuccess(
                status: true,
                message: 'Blog deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function HandelBlogResource($blogs, $viewType, $paginate)
    {
        $blogs = $blogs ?? collect();
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return BlogResource::collection($blogs);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return ApiBlogResource::collection($blogs);
        } else {
            return BlogResource::collection($blogs);
        }
    }

    public function HandelBlogDetailResource($blog, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return BlogDetailResource::make($blog);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return ApiBlogDetailResource::make($blog);
        } else {
            return BlogDetailResource::make($blog);
        }
    }
}
