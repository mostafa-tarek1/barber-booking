<?php

namespace App\Modules\Rooms\Http\Controllers\Dashboard\RoomAdditional;

use App\Modules\Rooms\Http\Requests\RoomAdditional\RoomAdditionalIdRequest;
use App\Modules\Rooms\Http\Requests\RoomAdditional\CreateRoomAdditionalRequest;
use App\Modules\Rooms\Http\Requests\RoomAdditional\FetchRoomAdditionalRequest;
use App\Modules\Rooms\Http\Requests\RoomAdditional\UpdateRoomAdditionalRequest;
use App\Modules\Rooms\Application\UseCases\RoomAdditional\RoomAdditionalUseCase;
use App\Http\Controllers\Controller;


class RoomAdditionalController extends Controller
{
    protected $roomAdditionalUseCase;

    public function __construct(RoomAdditionalUseCase $roomAdditionalUseCase)
    {
        $this->roomAdditionalUseCase = $roomAdditionalUseCase;
    }

    /**
     *  @OA\Info(
     *     title="RoomAdditional API",
     *     version="1.1",
     *     description="API documentation for managing RoomAdditionals , video and lessons."
     * )
     * @OA\Post(
     *     path="/dashboard/fetch_roomAdditionals",
     *     summary="Fetch a list of roomAdditionals",
     *     tags={"Dashboard RoomAdditional"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", nullable=true, description="Filter by roomAdditional title", example="Math 101"),
     *             @OA\Property(property="type", type="string", nullable=true, description="Filter by roomAdditional type", example="online"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Filter by parent roomAdditional ID", example=1),
     *             @OA\Property(property="code", type="string", nullable=true, description="Filter by roomAdditional code", example="MATH101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of roomAdditionals",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function fetchRoomAdditionals(FetchRoomAdditionalRequest $request)
    {
        return $this->roomAdditionalUseCase->fetchRoomAdditionals($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/fetch_roomAdditional_details",
     *     summary="Fetch details of a specific roomAdditional",
     *     tags={"Dashboard RoomAdditional"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="roomAdditional_id", type="integer", description="The ID of the roomAdditional", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RoomAdditional details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RoomAdditional not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid roomAdditional ID"
     *     )
     * )
     */
    public function fetchRoomAdditionalDetails(RoomAdditionalIdRequest $request)
    {
        // dd($request->toDTO());
        return $this->roomAdditionalUseCase->fetchRoomAdditionalDetails($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/create_roomAdditional",
     *     summary="Create a new roomAdditional",
     *     tags={"Dashboard RoomAdditional"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="translations", type="array", description="RoomAdditional translations", @OA\Items(type="object")),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent roomAdditional ID", example=1),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Organization ID", example=1),
     *             @OA\Property(property="stage_id", type="integer", nullable=true, description="Stage ID", example=1),
     *             @OA\Property(property="subject_id", type="integer", nullable=true, description="Subject ID", example=1),
     *             @OA\Property(property="type", type="string", description="RoomAdditional type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="RoomAdditional status", example="active"),
     *             @OA\Property(property="is_private", type="boolean", nullable=true, description="Is the roomAdditional private?", example=true),
     *             @OA\Property(property="has_website", type="boolean", nullable=true, description="Does the roomAdditional have a website?", example=false),
     *             @OA\Property(property="has_app", type="boolean", nullable=true, description="Does the roomAdditional have an app?", example=false),
     *             @OA\Property(property="start_date", type="string", format="date", description="RoomAdditional start date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", description="RoomAdditional end date", example="2025-06-30"),
     *             @OA\Property(property="image", type="string", nullable=true, description="RoomAdditional image URL or file", example="roomAdditional.jpg"),
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
     *         description="RoomAdditional created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function createRoomAdditional(CreateRoomAdditionalRequest $request)
    {
        // dd($request->toDTO());
        return $this->roomAdditionalUseCase->createRoomAdditional($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/update_roomAdditional",
     *     summary="Update an existing roomAdditional",
     *     tags={"Dashboard RoomAdditional"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="roomAdditional_id", type="integer", description="The ID of the roomAdditional to update", example=1),
     *             @OA\Property(property="translations", type="array", nullable=true, description="RoomAdditional translations", @OA\Items(type="object")),
     *             @OA\Property(property="code", type="string", nullable=true, maxLength=255, description="RoomAdditional code", example="MATH101"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent roomAdditional ID", example=1),
     *             @OA\Property(property="type", type="string", nullable=true, description="RoomAdditional type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="RoomAdditional status", example="active"),
     *             @OA\Property(property="image", type="string", nullable=true, description="RoomAdditional image URL or file", example="roomAdditional.jpg"),
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
     *         description="RoomAdditional updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RoomAdditional not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function updateRoomAdditional(UpdateRoomAdditionalRequest $request)
    {
        return $this->roomAdditionalUseCase->updateRoomAdditional($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/delete_roomAdditional",
     *     summary="Delete a roomAdditional",
     *     tags={"Dashboard RoomAdditional"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="roomAdditional_id", type="integer", description="The ID of the roomAdditional to delete", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="RoomAdditional deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RoomAdditional not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid roomAdditional ID"
     *     )
     * )
     */
    public function deleteRoomAdditional(RoomAdditionalIdRequest $request)
    {
        return $this->roomAdditionalUseCase->deleteRoomAdditional($request->toDTO())->response();
    }
}
