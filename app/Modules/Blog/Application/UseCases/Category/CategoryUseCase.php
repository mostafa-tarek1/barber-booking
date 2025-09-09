<?php

namespace App\Modules\Blog\Application\UseCases\Category;


use App\Modules\Blog\Application\DTOS\Category\CategoryDTO;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Blog\Http\Resources\Category\CategoryResource;
use App\Modules\Blog\Application\DTOS\Category\CategoryFilterDTO;
use App\Modules\Blog\Infrastructure\Persistence\Repositories\Category\CategoryRepository;



class CategoryUseCase
{

    protected $categoryRepository;


    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function fetchCategories(CategoryFilterDTO $categoryFilterDTO): DataStatus
    {
        try {
            $categorys = $this->categoryRepository->filter(
                $categoryFilterDTO,
                operator: 'like',
                translatableFields: ['title'],
                paginate: $categoryFilterDTO->paginate,
                limit: $categoryFilterDTO->limit
            );
            $resource = $categoryFilterDTO->paginate ? CategoryResource::collection($categorys)->response()->getData(true) : CategoryResource::collection($categorys);
            return DataSuccess(
                status: true,
                message: 'Category fetched successfully',
                data: $resource,
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
    public function fetchLatestCategories(CategoryFilterDTO $categoryFilterDTO): DataStatus
    {
        try {
            $categorys = $this->categoryRepository->filter(
                $categoryFilterDTO,
                operator: 'like',
                translatableFields: ['title'],
                paginate: $categoryFilterDTO->paginate,
                limit: $categoryFilterDTO->limit
            )->sortByDesc('created_at')->take($categoryFilterDTO->limit);
            $resource = $categoryFilterDTO->paginate ? CategoryResource::collection($categorys)->response()->getData(true) : CategoryResource::collection($categorys);
            return DataSuccess(
                status: true,
                message: 'Category fetched successfully',
                data: $resource,
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }


    public function fetchCategoryDetails(CategoryFilterDTO $categoryFilterDTO): DataStatus
    {
        try {
            $category = $this->categoryRepository->getById($categoryFilterDTO->category_id);
            return DataSuccess(
                status: true,
                message: 'Course Category fetched successfully',
                data: new CategoryResource($category)
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
            $category = $this->categoryRepository->create($categoryDTO);

            return DataSuccess(
                status: true,
                message: ' Category created successfully',
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
            $category = $this->categoryRepository->update($categoryDTO->category_id, $categoryDTO);

            return DataSuccess(
                status: true,
                message: ' Category updated successfully',
                data: new CategoryResource($category)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteCategory(CategoryFilterDTO $categoryFilterDTO): DataStatus
    {
        try {
            $category = $this->categoryRepository->delete($categoryFilterDTO->category_id);
            return DataSuccess(
                status: true,
                message: ' Category deleted successfully',
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
}
