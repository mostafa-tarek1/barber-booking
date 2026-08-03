<?php

namespace App\Modules\Booking\Http\Requests\Booking;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends BaseRequestAbstract
{
    protected $dtoClass = BookingDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        return [
            'phone' => ['required', 'string', 'min:9', 'max:13'],
            'email' => ['nullable', 'email', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'name' => ['required', 'string', 'max:255'],
            'from_hour' => ['required', 'date_format:H:i'],
            'to_hour' => ['nullable', 'date_format:H:i', 'after:from_hour'],
            'status' => ['sometimes', Rule::enum(BookingStatusEnum::class)],
            'services' => ['nullable', 'array', 'required_without:packages'],
            'services.*' => ['integer', 'distinct', 'exists:services,id'],
            'packages' => ['nullable', 'array', 'required_without:services'],
            'packages.*' => ['integer', 'distinct', 'exists:packages,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0.01', 'decimal:0,2'],
            'deposit_payment_method' => [
                'required_with:deposit_amount',
                'nullable',
                Rule::in(['cash', 'card', 'wallet']),
            ],
        ];
    }
}
