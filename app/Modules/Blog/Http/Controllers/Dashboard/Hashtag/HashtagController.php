<?php

namespace App\Modules\Blog\Http\Controllers\Dashboard\Hashtag;

use App\Modules\Blog\Application\UseCases\Hashtag\HashtagUseCase;
use App\Modules\Blog\Http\Requests\Hashtag\CreateHashtagRequest;
use App\Modules\Blog\Http\Requests\Hashtag\FetchHashtagRequest;
use App\Modules\Blog\Http\Requests\Hashtag\HashtagIdRequest;
use App\Modules\Blog\Http\Requests\Hashtag\UpdateHashtagRequest;
use App\Http\Controllers\Controller;

class HashtagController extends Controller
{
    protected $hashtagUseCase;

    public function __construct(HashtagUseCase $hashtagUseCase)
    {
        $this->hashtagUseCase = $hashtagUseCase;
    }

    /**
     *  @OA\Info(
     *     title="Hashtag API",
     *     version="1.1",
     *     description="API documentation for managing Hashtags , video and lessons."
     * )
     * @OA\Post(
     *     path="/dashboard/fetch_hashtags",
     *     summary="Fetch a list of hashtags",
     *     tags={"Dashboard Hashtag"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", nullable=true, description="Filter by hashtag title", example="Math 101"),
     *             @OA\Property(property="type", type="string", nullable=true, description="Filter by hashtag type", example="online"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Filter by parent hashtag ID", example=1),
     *             @OA\Property(property="code", type="string", nullable=true, description="Filter by hashtag code", example="MATH101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of hashtags",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function fetchHashtags(FetchHashtagRequest $request)
    {
        return $this->hashtagUseCase->fetchHashtags($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/fetch_hashtag_details",
     *     summary="Fetch details of a specific hashtag",
     *     tags={"Dashboard Hashtag"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="hashtag_id", type="integer", description="The ID of the hashtag", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hashtag details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hashtag not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid hashtag ID"
     *     )
     * )
     */
    public function fetchHashtagDetails(HashtagIdRequest $request)
    {
        return $this->hashtagUseCase->fetchHashtagDetails($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/create_hashtag",
     *     summary="Create a new hashtag",
     *     tags={"Dashboard Hashtag"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="translations", type="array", description="Hashtag translations", @OA\Items(type="object")),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent hashtag ID", example=1),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Organization ID", example=1),
     *             @OA\Property(property="stage_id", type="integer", nullable=true, description="Stage ID", example=1),
     *             @OA\Property(property="subject_id", type="integer", nullable=true, description="Subject ID", example=1),
     *             @OA\Property(property="type", type="string", description="Hashtag type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Hashtag status", example="active"),
     *             @OA\Property(property="is_private", type="boolean", nullable=true, description="Is the hashtag private?", example=true),
     *             @OA\Property(property="has_website", type="boolean", nullable=true, description="Does the hashtag have a website?", example=false),
     *             @OA\Property(property="has_app", type="boolean", nullable=true, description="Does the hashtag have an app?", example=false),
     *             @OA\Property(property="start_date", type="string", format="date", description="Hashtag start date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", description="Hashtag end date", example="2025-06-30"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Hashtag image URL or file", example="hashtag.jpg"),
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
     *         description="Hashtag created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function createHashtag(CreateHashtagRequest $request)
    {
        return $this->hashtagUseCase->createHashtag($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/update_hashtag",
     *     summary="Update an existing hashtag",
     *     tags={"Dashboard Hashtag"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="hashtag_id", type="integer", description="The ID of the hashtag to update", example=1),
     *             @OA\Property(property="translations", type="array", nullable=true, description="Hashtag translations", @OA\Items(type="object")),
     *             @OA\Property(property="code", type="string", nullable=true, maxLength=255, description="Hashtag code", example="MATH101"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent hashtag ID", example=1),
     *             @OA\Property(property="type", type="string", nullable=true, description="Hashtag type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Hashtag status", example="active"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Hashtag image URL or file", example="hashtag.jpg"),
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
     *         description="Hashtag updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hashtag not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */



    public function updateHashtag(UpdateHashtagRequest $request)
    {
        return $this->hashtagUseCase->updateHashtag($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/delete_hashtag",
     *     summary="Delete a hashtag",
     *     tags={"Dashboard Hashtag"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="hashtag_id", type="integer", description="The ID of the hashtag to delete", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Hashtag deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hashtag not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid hashtag ID"
     *     )
     * )
     */
    public function deleteHashtag(HashtagIdRequest $request)
    {
        return $this->hashtagUseCase->deleteHashtag($request->toDTO())->response();
    }
}
