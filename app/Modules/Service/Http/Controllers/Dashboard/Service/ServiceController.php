<?php

namespace App\Modules\Service\Http\Controllers\Dashboard\Service;

use App\Modules\Service\Http\Requests\Service\ServiceIdRequest;
use App\Modules\Service\Http\Requests\Service\CreateServiceRequest;
use App\Modules\Service\Http\Requests\Service\FetchServiceRequest;
use App\Modules\Service\Http\Requests\Service\UpdateServiceRequest;
use App\Modules\Service\Application\UseCases\Service\ServiceUseCase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ServiceController extends Controller
{
    protected $serviceUseCase;

    public function __construct(ServiceUseCase $serviceUseCase)
    {
        $this->serviceUseCase = $serviceUseCase;
    }

    /**
     *  @OA\Info(
     *     title="Service API",
     *     version="1.1",
     *     description="API documentation for managing Services , video and lessons."
     * )
     * @OA\Post(
     *     path="/dashboard/fetch_services",
     *     summary="Fetch a list of services",
     *     tags={"Dashboard Service"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", nullable=true, description="Filter by service title", example="Math 101"),
     *             @OA\Property(property="type", type="string", nullable=true, description="Filter by service type", example="online"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Filter by parent service ID", example=1),
     *             @OA\Property(property="code", type="string", nullable=true, description="Filter by service code", example="MATH101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of services",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function fetchServices(FetchServiceRequest $request)
    {
        return $this->serviceUseCase->fetchServices($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/fetch_service_details",
     *     summary="Fetch details of a specific service",
     *     tags={"Dashboard Service"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="service_id", type="integer", description="The ID of the service", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid service ID"
     *     )
     * )
     */
    public function fetchServiceDetails(ServiceIdRequest $request)
    {
        return $this->serviceUseCase->fetchServiceDetails($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/create_service",
     *     summary="Create a new service",
     *     tags={"Dashboard Service"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="translations", type="array", description="Service translations", @OA\Items(type="object")),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent service ID", example=1),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Organization ID", example=1),
     *             @OA\Property(property="stage_id", type="integer", nullable=true, description="Stage ID", example=1),
     *             @OA\Property(property="subject_id", type="integer", nullable=true, description="Subject ID", example=1),
     *             @OA\Property(property="type", type="string", description="Service type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Service status", example="active"),
     *             @OA\Property(property="is_private", type="boolean", nullable=true, description="Is the service private?", example=true),
     *             @OA\Property(property="has_website", type="boolean", nullable=true, description="Does the service have a website?", example=false),
     *             @OA\Property(property="has_app", type="boolean", nullable=true, description="Does the service have an app?", example=false),
     *             @OA\Property(property="start_date", type="string", format="date", description="Service start date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", description="Service end date", example="2025-06-30"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Service image URL or file", example="service.jpg"),
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
     *         description="Service created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function createService(CreateServiceRequest $request)
    {
        return $this->serviceUseCase->createService($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/update_service",
     *     summary="Update an existing service",
     *     tags={"Dashboard Service"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="service_id", type="integer", description="The ID of the service to update", example=1),
     *             @OA\Property(property="translations", type="array", nullable=true, description="Service translations", @OA\Items(type="object")),
     *             @OA\Property(property="code", type="string", nullable=true, maxLength=255, description="Service code", example="MATH101"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent service ID", example=1),
     *             @OA\Property(property="type", type="string", nullable=true, description="Service type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Service status", example="active"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Service image URL or file", example="service.jpg"),
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
     *         description="Service updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function updateService(UpdateServiceRequest $request)
    {
        return $this->serviceUseCase->updateService($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/delete_service",
     *     summary="Delete a service",
     *     tags={"Dashboard Service"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="service_id", type="integer", description="The ID of the service to delete", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Service deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid service ID"
     *     )
     * )
     */
    public function deleteService(ServiceIdRequest $request)
    {
        return $this->serviceUseCase->deleteService($request->toDTO())->response();
    }
}
