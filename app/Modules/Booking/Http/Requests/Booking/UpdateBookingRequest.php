<?php

namespace App\Modules\Booking\Http\Requests\Booking;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends BaseRequestAbstract
{
    protected $dtoClass = BookingDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'phone' => ['nullable', 'string', 'min:9', 'max:13'],
            'email' => ['nullable', 'email', 'max:255'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
            'name' => ['nullable', 'string', 'max:255'],
            'from_hour' => ['nullable', 'date_format:H:i'],
            'to_hour' => ['nullable', 'date_format:H:i', 'after:from_hour'],
            'status' => ['sometimes', Rule::enum(BookingStatusEnum::class)],
            'services' => ['sometimes', 'array'],
            'services.*' => ['integer', 'distinct', 'exists:services,id'],
            'packages' => ['sometimes', 'array'],
            'packages.*' => ['integer', 'distinct', 'exists:packages,id'],
            'employee_id' => ['sometimes', 'exists:employees,id'],
        ];
    }
}
