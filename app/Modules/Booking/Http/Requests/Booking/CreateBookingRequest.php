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
            'phone' => ['nullable', 'string', 'min:9', 'max:13'],
            'email' => ['nullable', 'email', 'max:255'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
            'name' => ['nullable', 'string', 'max:255'],
            'from_hour' => ['nullable', 'date_format:H:i'],
            'to_hour' => ['nullable', 'date_format:H:i', 'after:from_hour'],
            'status' => ['sometimes', Rule::enum(BookingStatusEnum::class)],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['exists:services,id'],
            'employee_id' => ['required', 'exists:employees,id'],
        ];
    }
}
