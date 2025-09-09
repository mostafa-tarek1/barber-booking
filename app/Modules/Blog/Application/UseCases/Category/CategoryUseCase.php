<?php

namespace App\Modules\Blog\Application\UseCases\Category;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Blog\Application\DTOS\Category\CategoryDTO;
use App\Modules\Blog\Http\Resources\Api\Category\WebSiteCategoryDetailResource;
use App\Modules\Blog\Http\Resources\Api\Category\WebSiteCategoryResource;
use App\Modules\Blog\Http\Resources\Category\CategoryDetailResource;
use App\Modules\Blog\Http\Resources\Category\CategoryResource;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog\BlogHashtagsRepository;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Category\CategoryRepository;
use App\Modules\Blog\Application\DTOS\Category\CategoryFilterDTO;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Hashtag\HashtagRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CategoryUseCase
{

    protected $categoryRepository;

    protected $employee;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;

        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchCategories(CategoryFilterDTO $categoryFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // dd($categoryFilterDto);
            $categories = $this->categoryRepository->filter(
                $categoryFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $categoryFilterDto->paginate,
                limit: $categoryFilterDto->limit
            );
            // dd($categories);
            $resource = $this->HandelCategoryResource(
                $categories,
                $view,
                $categoryFilterDto->paginate
            );
            return DataSuccess(
                status: true,
                message: 'Categorys fetched successfully',
                data: $categoryFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchCategoryDetails(CategoryFilterDTO $categoryFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $category = $this->categoryRepository->getById($categoryFilterDto->category_id);
            $resource = $this->HandelCategoryDetailResource(
                $category,
                $view,
                false
            );
            return DataSuccess(
                status: true,
                message: 'Category fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createCategory(CategoryDTO $categoryDTO): DataStatus
    {
        try {
            $categoryDTO->created_by = $this->employee->id;
            // dd($categoryDTO);
            $category = $this->categoryRepository->create($categoryDTO);
            $category->refresh();
            return DataSuccess(
                status: true,
                message: 'category created successfully',
                data: new CategoryResource($category)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function updateCategory(CategoryDTO $categoryDTO): DataStatus
    {
        try {
            Log::info('categoryDTO', $categoryDTO->toArray());
            $categoryDTO->updated_by = $this->employee->id;
            $category = $this->categoryRepository->update($categoryDTO->category_id, $categoryDTO);
            $categoryDTO->category_id = $category->id;
            $category->refresh();
            return DataSuccess(
                status: true,
                message: 'Category updated successfully',
                data: new CategoryResource($category)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteCategory(CategoryFilterDTO $categoryFilterDto): DataStatus
    {
        try {
            /** @var Category|null $category */
            $category = $this->categoryRepository->getById($categoryFilterDto->category_id);

            if (!$category) {
                return DataFailed(
                    status: false,
                    statusCode: 404,
                    message: 'Category not found'
                );
            }

            if ($category->children && $category->children->count() > 0) {
                return DataFailed(
                    status: false,
                    statusCode: 400,
                    message: 'Category has children'
                );
            }

            $this->categoryRepository->delete($categoryFilterDto->category_id);

            return DataSuccess(
                status: true,
                message: 'Category deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function HandelCategoryResource($categories, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return CategoryResource::collection($categories);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return WebSiteCategoryResource::collection($categories);
        } else {
            return CategoryResource::collection($categories);
        }
    }

    public function HandelCategoryDetailResource($category, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return CategoryDetailResource::make($category);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return WebSiteCategoryDetailResource::make($category);
        } else {
            return CategoryDetailResource::collection($category);
        }
    }
}
