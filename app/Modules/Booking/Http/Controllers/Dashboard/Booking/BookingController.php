<?php

namespace App\Modules\Booking\Http\Controllers\Dashboard\Booking;

use App\Modules\Booking\Http\Requests\Booking\BookingIdRequest;
use App\Modules\Booking\Http\Requests\Booking\CreateBookingRequest;
use App\Modules\Booking\Http\Requests\Booking\FetchBookingRequest;
use App\Modules\Booking\Http\Requests\Booking\UpdateBookingRequest;
use App\Modules\Booking\Application\UseCases\Booking\BookingUseCase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;

class BookingController extends Controller
{
    protected $bookingUseCase;

    public function __construct(BookingUseCase $bookingUseCase)
    {
        $this->bookingUseCase = $bookingUseCase;
    }

    /**
     *  @OA\Info(
     *     title="Booking API",
     *     version="1.1",
     *     description="API documentation for managing Booking , video and lessons."
     * )
     * @OA\Post(
     *     path="/dashboard/fetch_booking",
     *     summary="Fetch a list of booking",
     *     tags={"Dashboard Booking"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", nullable=true, description="Filter by booking title", example="Math 101"),
     *             @OA\Property(property="type", type="string", nullable=true, description="Filter by booking type", example="online"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Filter by parent booking ID", example=1),
     *             @OA\Property(property="code", type="string", nullable=true, description="Filter by booking code", example="MATH101")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of booking",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function fetchBooking(FetchBookingRequest $request)
    {
        return $this->bookingUseCase->fetchBooking($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/fetch_booking_details",
     *     summary="Fetch details of a specific booking",
     *     tags={"Dashboard Booking"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="booking_id", type="integer", description="The ID of the booking", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid booking ID"
     *     )
     * )
     */
    public function fetchBookingDetails(BookingIdRequest $request)
    {
        // dd($request->toDTO());
        return $this->bookingUseCase->fetchBookingDetails($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/create_booking",
     *     summary="Create a new booking",
     *     tags={"Dashboard Booking"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="translations", type="array", description="Booking translations", @OA\Items(type="object")),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent booking ID", example=1),
     *             @OA\Property(property="organization_id", type="integer", nullable=true, description="Organization ID", example=1),
     *             @OA\Property(property="stage_id", type="integer", nullable=true, description="Stage ID", example=1),
     *             @OA\Property(property="subject_id", type="integer", nullable=true, description="Subject ID", example=1),
     *             @OA\Property(property="type", type="string", description="Booking type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Booking status", example="active"),
     *             @OA\Property(property="is_private", type="boolean", nullable=true, description="Is the booking private?", example=true),
     *             @OA\Property(property="has_website", type="boolean", nullable=true, description="Does the booking have a website?", example=false),
     *             @OA\Property(property="has_app", type="boolean", nullable=true, description="Does the booking have an app?", example=false),
     *             @OA\Property(property="start_date", type="string", format="date", description="Booking start date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", description="Booking end date", example="2025-06-30"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Booking image URL or file", example="booking.jpg"),
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
     *         description="Booking created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function createBooking(CreateBookingRequest $request)
    {
        return $this->bookingUseCase->createBooking($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/update_booking",
     *     summary="Update an existing booking",
     *     tags={"Dashboard Booking"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="booking_id", type="integer", description="The ID of the booking to update", example=1),
     *             @OA\Property(property="translations", type="array", nullable=true, description="Booking translations", @OA\Items(type="object")),
     *             @OA\Property(property="code", type="string", nullable=true, maxLength=255, description="Booking code", example="MATH101"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent booking ID", example=1),
     *             @OA\Property(property="type", type="string", nullable=true, description="Booking type", example="online"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"active", "inactive"}, description="Booking status", example="active"),
     *             @OA\Property(property="image", type="string", nullable=true, description="Booking image URL or file", example="booking.jpg"),
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
     *         description="Booking updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or validation error"
     *     )
     * )
     */
    public function updateBooking(UpdateBookingRequest $request)
    {
        return $this->bookingUseCase->updateBooking($request->toDTO())->response();
    }

    /**
     * @OA\Post(
     *     path="/dashboard/delete_booking",
     *     summary="Delete a booking",
     *     tags={"Dashboard Booking"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="booking_id", type="integer", description="The ID of the booking to delete", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Booking deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid booking ID"
     *     )
     * )
     */
    public function deleteBooking(BookingIdRequest $request)
    {
        return $this->bookingUseCase->deleteBooking($request->toDTO())->response();
    }

     /**
     * Check available time slots for an employee
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date',
            'from_time' => 'required|date_format:H:i',
            'to_time' => 'required|date_format:H:i|after:from_time',
        ]);

        $suggestedSlots = $this->bookingUseCase->getAvailableTimeSlots(
            $request->employee_id,
            $request->date,
            $request->from_time,
            $request->to_time
        );

        return response()->json([
            'status' => 200,
            'message' => 'Available time slots retrieved successfully',
            'data' => [
                'suggested_slots' => $suggestedSlots
            ]
        ]);
    }
}
