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
            'status_text' => $this->getStatusText(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),

            // العلاقات مع تحسين الأداء
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => trim($this->createdBy->first_name . ' ' . $this->createdBy->last_name)
                ];
            }),
            'updated_by' => $this->whenLoaded('updatedBy', function () {
                return [
                    'id' => $this->updatedBy->id,
                    'name' => trim($this->updatedBy->first_name . ' ' . $this->updatedBy->last_name)
                ];
            }),
            'services' => $this->whenLoaded('services', function () {
                return $this->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->price,
                        'duration' => $service->duration,
                        'employee_id' => $service->pivot->employee_id ?? null
                    ];
                });
            }),
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => trim($this->employee->first_name . ' ' . $this->employee->last_name)
                ];
            }),

            // معلومات إضافية محسنة
            'total_price' => $this->whenLoaded('services', function () {
                return $this->services->sum('price');
            }),
            'total_duration' => $this->whenLoaded('services', function () {
                return $this->services->sum('duration');
            }),
        ];
    }

    /**
     * Get status text in Arabic
     */
    private function getStatusText(): string
    {
        return match ($this->status) {
            0 => 'في الانتظار',
            1 => 'مؤكد',
            2 => 'ملغي',
            3 => 'مكتمل',
            default => 'غير محدد'
        };
    }
}
