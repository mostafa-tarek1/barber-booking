<?php

namespace App\Modules\Booking\Http\Resources\Api\Booking;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use App\Modules\Rooms\Http\Resources\Api\RoomResource;
use App\Modules\Service\Http\Resources\Api\ServiceResource;
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
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'employee' => new AdminResource($this->whenLoaded('employee')),
        ];
    }
}
