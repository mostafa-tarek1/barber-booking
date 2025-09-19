<?php

namespace App\Modules\Booking\Http\Resources\Dashboard\Booking;

use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use App\Modules\Rooms\Http\Resources\Dashboard\Room\GeneralRoomResource;
use App\Modules\Rooms\Http\Resources\Dashboard\Room\RoomResource;
use App\Modules\Rooms\Http\Resources\RoomAdditional\GeneralRoomAdditionalResource;
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
            /* 'rooms' => $this->rooms?->map(fn($room) => [
                'id' => $room->id,
                'title' => $room->title,
            ]) ?? [], */

            'rooms' => GeneralRoomResource::collection($this->rooms) ?? [],

            // dd($this->room_additionals),
            /* 'room_additionals' => $this->room_additional?->map(fn($additional) => [
                'id' => $additional->id,
                'title' => $additional->title,
            ]) ?? [], */

            'room_additionals' => GeneralRoomAdditionalResource::collection($this->room_additionals) ?? [],

        ];
    }
}
