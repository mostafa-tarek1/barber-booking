<?php

namespace App\Modules\Rooms\Http\Controllers\Dashboard\RoomLocation;

use App\Modules\Rooms\Application\UseCases\RoomLocation\RoomLocationUseCase;
use App\Modules\Rooms\Http\Requests\RoomLocation\CreateRoomLocationRequest;
use App\Modules\Rooms\Http\Requests\RoomLocation\FetchRoomLocationRequest;
use App\Modules\Rooms\Http\Requests\RoomLocation\RoomLocationIdRequest;
use App\Http\Controllers\Controller;
use App\Modules\Rooms\Http\Requests\RoomLocation\UpdateRoomLocationRequest;


class RoomLocationController extends Controller
{
    protected $roomLocationUseCase;

    public function __construct(RoomLocationUseCase $roomLocationUseCase)
    {
        $this->roomLocationUseCase = $roomLocationUseCase;
    }

    /**
     *  @OA\Info(
     *     title="RoomLocation API",
     *     version="1.1",
     *     description="API documentation for managing Rooms , video and lessons."
     * )
     * @OA\Post(
     *     path="/dashboard/fetch_rooms",
     *     summary="Fetch a list of rooms",
     *     tags={"Dashboard RoomLocation"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", nullable=true, description="Filter by room title", example="Math 101"),
     *             @OA\Property(property="type", type="string", nullable=true, description="Filter by room type", example="online"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Filter by parent room ID", example=1),
     *             @OA\Property(property="code", type="string", nullable=true, description="Filter by room code", example="MATH101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of rooms",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function fetchRoomLocations(FetchRoomLocationRequest $request)
    {
        return $this->roomLocationUseCase->fetchRoomLocations($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/fetch_room_details",
     *     summary="Fetch details of a specific room",
     *     tags={"Dashboard RoomLocation"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="room_id", type="integer", description="The ID of the room", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="RoomLocation details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RoomLocation not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid room ID"
     *     )
     * )
     */
    public function fetchRoomLocationDetails(RoomLocationIdRequest $request)
    {
        // dd($request->toDTO());
        return $this->roomLocationUseCase->fetchRoomLocationDetails($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/create_room",
     *     summary="Create a new room",
     *     tags={"Dashboard RoomLocation"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="translations", type="array", description="RoomLocation translations", @OA\Items(type="object")),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent room ID", example=1),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Organization ID", example=1),
     *             @OA\Property(property="stage_id", type="integer", nullable=true, description="Stage ID", example=1),
     *             @OA\Property(property="subject_id", type="integer", nullable=true, description="Subject ID", example=1),
     *             @OA\Property(property="type", type="string", description="RoomLocation type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="RoomLocation status", example="active"),
     *             @OA\Property(property="is_private", type="boolean", nullable=true, description="Is the room private?", example=true),
     *             @OA\Property(property="has_website", type="boolean", nullable=true, description="Does the room have a website?", example=false),
     *             @OA\Property(property="has_app", type="boolean", nullable=true, description="Does the room have an app?", example=false),
     *             @OA\Property(property="start_date", type="string", format="date", description="RoomLocation start date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", description="RoomLocation end date", example="2025-06-30"),
     *             @OA\Property(property="image", type="string", nullable=true, description="RoomLocation image URL or file", example="room.jpg"),
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
     *         description="RoomLocation created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function createRoomLocation(CreateRoomLocationRequest $request)
    {
        return $this->roomLocationUseCase->createRoomLocation($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/update_room",
     *     summary="Update an existing room",
     *     tags={"Dashboard RoomLocation"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="room_id", type="integer", description="The ID of the room to update", example=1),
     *             @OA\Property(property="translations", type="array", nullable=true, description="RoomLocation translations", @OA\Items(type="object")),
     *             @OA\Property(property="code", type="string", nullable=true, maxLength=255, description="RoomLocation code", example="MATH101"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent room ID", example=1),
     *             @OA\Property(property="type", type="string", nullable=true, description="RoomLocation type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="RoomLocation status", example="active"),
     *             @OA\Property(property="image", type="string", nullable=true, description="RoomLocation image URL or file", example="room.jpg"),
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
     *         description="RoomLocation updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RoomLocation not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function updateRoomLocation(UpdateRoomLocationRequest $request)
    {
        return $this->roomLocationUseCase->updateRoomLocation($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/delete_room",
     *     summary="Delete a room",
     *     tags={"Dashboard RoomLocation"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="room_id", type="integer", description="The ID of the room to delete", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="RoomLocation deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="RoomLocation not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid room ID"
     *     )
     * )
     */
    public function deleteRoomLocation(RoomLocationIdRequest $request)
    {
        return $this->roomLocationUseCase->deleteRoomLocation($request->toDTO())->response();
    }
}
