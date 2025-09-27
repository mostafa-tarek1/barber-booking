<?php

namespace App\Modules\Booking\Http\Resources\Dashboard\Booking;

use Illuminate\Http\Request;
class BookingDetailResource extends BookingResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        return $data;
    }
}
