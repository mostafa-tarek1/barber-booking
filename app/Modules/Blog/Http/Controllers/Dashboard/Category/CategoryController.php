<?php

namespace App\Modules\Blog\Http\Controllers\Dashboard\Category;

use App\Modules\Blog\Http\Requests\Category\CategoryIdRequest;
use App\Modules\Blog\Http\Requests\Category\CreateCategoryRequest;
use App\Modules\Blog\Http\Requests\Category\FetchCategoryRequest;
use App\Modules\Blog\Http\Requests\Category\UpdateCategoryRequest;
use App\Modules\Blog\Application\UseCases\Category\CategoryUseCase;
use Illuminate\Http\Request;
use GuzzleHttp\Promise\Create;
use App\Http\Controllers\Controller;


class CategoryController extends Controller
{
    protected $categoryUseCase;

    public function __construct(CategoryUseCase $categoryUseCase)
    {
        $this->categoryUseCase = $categoryUseCase;
    }

    /**
     *  @OA\Info(
     *     title="Category API",
     *     version="1.1",
     *     description="API documentation for managing Categorys , video and lessons."
     * )
     * @OA\Post(
     *     path="/dashboard/fetch_categorys",
     *     summary="Fetch a list of categorys",
     *     tags={"Dashboard Category"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", nullable=true, description="Filter by category title", example="Math 101"),
     *             @OA\Property(property="type", type="string", nullable=true, description="Filter by category type", example="online"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Filter by parent category ID", example=1),
     *             @OA\Property(property="code", type="string", nullable=true, description="Filter by category code", example="MATH101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of categorys",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function fetchCategories(FetchCategoryRequest $request)
    {
        return $this->categoryUseCase->fetchCategories($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/fetch_category_details",
     *     summary="Fetch details of a specific category",
     *     tags={"Dashboard Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="category_id", type="integer", description="The ID of the category", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid category ID"
     *     )
     * )
     */
    public function fetchCategoryDetails(CategoryIdRequest $request)
    {
        return $this->categoryUseCase->fetchCategoryDetails($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/create_category",
     *     summary="Create a new category",
     *     tags={"Dashboard Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="translations", type="array", description="Category translations", @OA\Items(type="object")),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent category ID", example=1),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Organization ID", example=1),
     *             @OA\Property(property="stage_id", type="integer", nullable=true, description="Stage ID", example=1),
     *             @OA\Property(property="subject_id", type="integer", nullable=true, description="Subject ID", example=1),
     *             @OA\Property(property="type", type="string", description="Category type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Category status", example="active"),
     *             @OA\Property(property="is_private", type="boolean", nullable=true, description="Is the category private?", example=true),
     *             @OA\Property(property="has_website", type="boolean", nullable=true, description="Does the category have a website?", example=false),
     *             @OA\Property(property="has_app", type="boolean", nullable=true, description="Does the category have an app?", example=false),
     *             @OA\Property(property="start_date", type="string", format="date", description="Category start date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", description="Category end date", example="2025-06-30"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Category image URL or file", example="category.jpg"),
     *             @OA\Property(
     *                 property="video",
     *                 type="object",
     *                 nullable=true,
     *                 description="Video details",
     *                 @OA\Property(property="is_file", type="integer", enum={0, 1}, nullable=true, description="Is the video a file? (1 = yes, 0 = no)", example=1),
     *                 @OA\Property(property="video_type", type="string", description="Type of video", example="mp4"),
     *                 @OA\Property(property="file", type="string", description="Video file (required if is_file = 1)", example="video.mp4"),
     *                 @OA\Property(property="link", type="string", description="Video link (required if is_file = 0)", example="https://example.com/video")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function createCategory(CreateCategoryRequest $request)
    {
        return $this->categoryUseCase->createCategory($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/update_category",
     *     summary="Update an existing category",
     *     tags={"Dashboard Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="category_id", type="integer", description="The ID of the category to update", example=1),
     *             @OA\Property(property="translations", type="array", nullable=true, description="Category translations", @OA\Items(type="object")),
     *             @OA\Property(property="code", type="string", nullable=true, maxLength=255, description="Category code", example="MATH101"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent category ID", example=1),
     *             @OA\Property(property="type", type="string", nullable=true, description="Category type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Category status", example="active"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Category image URL or file", example="category.jpg"),
     *             @OA\Property(
     *                 property="video",
     *                 type="object",
     *                 nullable=true,
     *                 description="Video details",
     *                 @OA\Property(property="is_file", type="integer", enum={0, 1}, nullable=true, description="Is the video a file? (1 = yes, 0 = no)", example=1),
     *                 @OA\Property(property="video_type", type="string", description="Type of video", example="mp4"),
     *                 @OA\Property(property="file", type="string", description="Video file (required if is_file = 1)", example="video.mp4"),
     *                 @OA\Property(property="link", type="string", description="Video link (required if is_file = 0)", example="https://example.com/video")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function updateCategory(UpdateCategoryRequest $request)
    {
        return $this->categoryUseCase->updateCategory($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/delete_category",
     *     summary="Delete a category",
     *     tags={"Dashboard Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="category_id", type="integer", description="The ID of the category to delete", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid category ID"
     *     )
     * )
     */
    public function deleteCategory(CategoryIdRequest $request)
    {
        return $this->categoryUseCase->deleteCategory($request->toDTO())->response();
    }
}
