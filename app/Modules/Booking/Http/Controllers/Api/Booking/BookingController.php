<?php

namespace App\Modules\Booking\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Booking\Application\Services\BarberAvailabilityService;
use App\Modules\Booking\Application\Services\BookingDepositService;
use App\Modules\Booking\Application\UseCases\Booking\BookingUseCase;
use App\Modules\Booking\Http\Requests\Booking\CreateBookingRequest;
use App\Support\BusinessDay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingUseCase;

    protected BarberAvailabilityService $availabilityService;

    public function __construct(BookingUseCase $bookingUseCase, BarberAvailabilityService $availabilityService)
    {
        $this->bookingUseCase = $bookingUseCase;
        $this->availabilityService = $availabilityService;
    }

    public function createBooking(CreateBookingRequest $request, BookingDepositService $depositService)
    {
        return $this->bookingUseCase->createBooking(
            $request->toDTO(),
            ViewTypeEnum::MOBILE,
            $depositService
        )->response();
    }

    /**
     * Check available time slots for an employee
     *
     * @return JsonResponse
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
                'suggested_slots' => $suggestedSlots,
            ],
        ]);
    }

    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'date' => ['required', 'date', 'after_or_equal:'.BusinessDay::currentDate()],
            'services' => ['nullable', 'array', 'required_without:packages'],
            'services.*' => ['integer', 'distinct', 'exists:services,id'],
            'packages' => ['nullable', 'array', 'required_without:services'],
            'packages.*' => ['integer', 'distinct', 'exists:packages,id'],
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Available slots fetched successfully',
            'data' => [
                'slots' => $this->availabilityService->availableSlots(
                    $validated['employee_id'],
                    $validated['date'],
                    $validated['services'] ?? [],
                    null,
                    $validated['packages'] ?? []
                ),
            ],
        ]);
    }
}
