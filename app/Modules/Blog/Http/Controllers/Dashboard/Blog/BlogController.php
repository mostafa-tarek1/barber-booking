<?php

namespace App\Modules\Blog\Http\Controllers\Dashboard\Blog;

use App\Modules\Blog\Http\Requests\Blog\BlogIdRequest;
use App\Modules\Blog\Http\Requests\Blog\CreateBlogRequest;
use App\Modules\Blog\Http\Requests\Blog\FetchBlogRequest;
use App\Modules\Blog\Http\Requests\Blog\UpdateBlogRequest;
use App\Modules\Blog\Application\UseCases\Blog\BlogUseCase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class BlogController extends Controller
{
    protected $blogUseCase;

    public function __construct(BlogUseCase $blogUseCase)
    {
        $this->blogUseCase = $blogUseCase;
    }

    /**
     *  @OA\Info(
     *     title="Blog API",
     *     version="1.1",
     *     description="API documentation for managing Blogs , video and lessons."
     * )
     * @OA\Post(
     *     path="/dashboard/fetch_blogs",
     *     summary="Fetch a list of blogs",
     *     tags={"Dashboard Blog"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", nullable=true, description="Filter by blog title", example="Math 101"),
     *             @OA\Property(property="type", type="string", nullable=true, description="Filter by blog type", example="online"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Filter by parent blog ID", example=1),
     *             @OA\Property(property="code", type="string", nullable=true, description="Filter by blog code", example="MATH101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of blogs",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function fetchBlogs(FetchBlogRequest $request)
    {
        return $this->blogUseCase->fetchBlogs($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/fetch_blog_details",
     *     summary="Fetch details of a specific blog",
     *     tags={"Dashboard Blog"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="blog_id", type="integer", description="The ID of the blog", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid blog ID"
     *     )
     * )
     */
    public function fetchBlogDetails(BlogIdRequest $request)
    {
        return $this->blogUseCase->fetchBlogDetails($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/create_blog",
     *     summary="Create a new blog",
     *     tags={"Dashboard Blog"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="translations", type="array", description="Blog translations", @OA\Items(type="object")),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent blog ID", example=1),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Organization ID", example=1),
     *             @OA\Property(property="stage_id", type="integer", nullable=true, description="Stage ID", example=1),
     *             @OA\Property(property="subject_id", type="integer", nullable=true, description="Subject ID", example=1),
     *             @OA\Property(property="type", type="string", description="Blog type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Blog status", example="active"),
     *             @OA\Property(property="is_private", type="boolean", nullable=true, description="Is the blog private?", example=true),
     *             @OA\Property(property="has_website", type="boolean", nullable=true, description="Does the blog have a website?", example=false),
     *             @OA\Property(property="has_app", type="boolean", nullable=true, description="Does the blog have an app?", example=false),
     *             @OA\Property(property="start_date", type="string", format="date", description="Blog start date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", description="Blog end date", example="2025-06-30"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Blog image URL or file", example="blog.jpg"),
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
     *         description="Blog created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function createBlog(CreateBlogRequest $request)
    {
        return $this->blogUseCase->createBlog($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/update_blog",
     *     summary="Update an existing blog",
     *     tags={"Dashboard Blog"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="blog_id", type="integer", description="The ID of the blog to update", example=1),
     *             @OA\Property(property="translations", type="array", nullable=true, description="Blog translations", @OA\Items(type="object")),
     *             @OA\Property(property="code", type="string", nullable=true, maxLength=255, description="Blog code", example="MATH101"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent blog ID", example=1),
     *             @OA\Property(property="type", type="string", nullable=true, description="Blog type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Blog status", example="active"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Blog image URL or file", example="blog.jpg"),
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
     *         description="Blog updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function updateBlog(UpdateBlogRequest $request)
    {
        return $this->blogUseCase->updateBlog($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/delete_blog",
     *     summary="Delete a blog",
     *     tags={"Dashboard Blog"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="blog_id", type="integer", description="The ID of the blog to delete", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Blog deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid blog ID"
     *     )
     * )
     */
    public function deleteBlog(BlogIdRequest $request)
    {
        return $this->blogUseCase->deleteBlog($request->toDTO())->response();
    }
}
