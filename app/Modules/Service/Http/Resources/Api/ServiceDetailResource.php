<?php

namespace App\Modules\Service\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ServiceDetailResource extends ServiceResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'bookings_count'   => $this->bookings_count ?? $this->bookings()->count(),
            'total_revenue'    => $this->total_revenue ?? $this->bookings()->sum('price'),
            'last_booking_at'  => optional($this->bookings()->latest('pivot_created_at')->first())->pivot?->created_at?->toDateTimeString(),
        ]);
    }
}
