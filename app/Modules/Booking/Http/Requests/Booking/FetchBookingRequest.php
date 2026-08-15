<?php

namespace App\Modules\Booking\Http\Requests\Booking;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Booking\Application\DTOS\Booking\BookingFilterDTO;
use Illuminate\Validation\Rule;

class FetchBookingRequest extends BaseRequestAbstract
{
    protected $dtoClass = BookingFilterDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'search' => ['nullable', 'string', 'max:255'],
            'booking_id' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'integer', Rule::in([0, 1, 2, 3])],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'sort' => ['nullable', Rule::in(['appointment', 'latest'])],
        ];
    }
}
