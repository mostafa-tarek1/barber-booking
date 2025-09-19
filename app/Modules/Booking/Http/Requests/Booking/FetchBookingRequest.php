<?php

namespace App\Modules\Booking\Http\Requests\Booking;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Booking\Application\DTOS\Booking\BookingFilterDTO;

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
            'word' => 'nullable|string|max:255',
            'booking_id' => 'nullable|integer',
        ];
    }
}
