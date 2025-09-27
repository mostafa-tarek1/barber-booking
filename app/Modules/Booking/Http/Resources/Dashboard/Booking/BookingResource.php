<?php

namespace App\Modules\Booking\Http\Resources\Dashboard\Booking;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use App\Modules\Rooms\Http\Resources\Dashboard\Room\GeneralRoomResource;
use App\Modules\Rooms\Http\Resources\Dashboard\Room\RoomResource;
use App\Modules\Rooms\Http\Resources\RoomAdditional\GeneralRoomAdditionalResource;
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
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_by' => $this->whenLoaded('creator', function () {
                return new AdminResource($this->creator);
            }),
            'updated_by' => $this->whenLoaded('updater', function () {
                return new AdminResource($this->updater);
            }),
            'services' => $this->whenLoaded('services', function () {
                return ServiceResource::collection($this->services);
            }),
            'employee' => $this->whenLoaded('employee', function () {
                return new AdminResource($this->employee);
            }),
        ];
    }
}
