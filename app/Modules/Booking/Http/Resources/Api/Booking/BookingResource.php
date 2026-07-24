<?php

namespace App\Modules\Booking\Http\Resources\Api\Booking;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use App\Modules\Rooms\Http\Resources\Api\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'date' => $this->date?->format('Y-m-d'),
            'from_hour' => $this->from_hour,
            'to_hour' => $this->to_hour,
            'time_slot' => "{$this->from_hour} - {$this->to_hour}",
            'status' => $this->status,
            'services' => $this->whenLoaded('services', fn () => $this->services->map(fn ($service) => [
                'id' => $service->id,
                'package_id' => $service->pivot->package_id,
                'name' => $service->pivot->name_snapshot ?: $service->name,
                'price' => $service->pivot->price_snapshot ?? $service->price,
                'duration' => $service->pivot->duration_snapshot ?? $service->duration,
                'quantity' => $service->pivot->quantity ?? 1,
            ])),
            'packages' => $this->whenLoaded('packages', fn () => $this->packages->map(fn ($package) => [
                'id' => $package->id,
                'name' => $package->pivot->name_snapshot ?: $package->name,
                'price' => $package->pivot->price_snapshot,
                'duration' => $package->pivot->duration_snapshot,
            ])),
            'employee' => new AdminResource($this->whenLoaded('employee')),
        ];
    }
}
