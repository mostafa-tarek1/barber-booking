<?php

namespace App\Modules\Booking\Http\Controllers\Api\Booking;

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

    public function createBooking(CreateBookingRequest $request)
    {
        return $this->bookingUseCase->createBooking($request->toDTO(), ViewTypeEnum::MOBILE->value)->response();
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
            'status' => true,
            'message' => 'Available time slots retrieved successfully',
            'data' => [
                'suggested_slots' => $suggestedSlots
            ]
        ]);
    }
}
