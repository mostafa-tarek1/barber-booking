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
}
