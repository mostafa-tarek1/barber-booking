<?php

namespace App\Modules\Booking\Http\Resources\Dashboard\Booking;

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
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),

            // العلاقات مع تحسين الأداء
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => trim($this->createdBy->first_name.' '.$this->createdBy->last_name),
                ];
            }),
            'updated_by' => $this->whenLoaded('updatedBy', function () {
                return [
                    'id' => $this->updatedBy->id,
                    'name' => trim($this->updatedBy->first_name.' '.$this->updatedBy->last_name),
                ];
            }),
            'services' => $this->whenLoaded('services', function () {
                return $this->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'package_id' => $service->pivot->package_id,
                        'name' => $service->translate('ar')?->name
                            ?: ($service->pivot->name_snapshot ?: $service->name),
                        'price' => $service->pivot->price_snapshot ?? $service->price,
                        'duration' => $service->pivot->duration_snapshot ?? $service->duration,
                        'quantity' => $service->pivot->quantity ?? 1,
                        'employee_id' => $service->pivot->employee_id ?? null,
                    ];
                });
            }),
            'packages' => $this->whenLoaded('packages', fn () => $this->packages->map(fn ($package) => [
                'id' => $package->id,
                'name' => $package->translate('ar')?->name
                    ?: ($package->pivot->name_snapshot ?: $package->name),
                'price' => $package->pivot->price_snapshot,
                'duration' => $package->pivot->duration_snapshot,
            ])),
            'deposit' => $this->whenLoaded('deposit', fn () => $this->deposit ? [
                'amount' => $this->deposit->amount,
                'payment_method' => $this->deposit->payment_method,
                'collected_at' => $this->deposit->collected_at?->toDateTimeString(),
            ] : null),
            'employee' => $this->whenLoaded('employee', function () {
                return $this->employee ? [
                    'id' => $this->employee->id,
                    'name' => trim($this->employee->first_name.' '.$this->employee->last_name),
                ] : null;
            }),

            // معلومات إضافية محسنة
            'total_price' => $this->whenLoaded('services', function () {
                $directServices = $this->services
                    ->filter(fn ($service) => ! $service->pivot->package_id)
                    ->sum(fn ($service) => ($service->pivot->price_snapshot ?? $service->price)
                        * ($service->pivot->quantity ?? 1));

                return $directServices + ($this->relationLoaded('packages')
                    ? $this->packages->sum(fn ($package) => $package->pivot->price_snapshot)
                    : 0);
            }),
            'total_duration' => $this->whenLoaded('services', function () {
                return $this->services->sum(fn ($service) => ($service->pivot->duration_snapshot ?? $service->duration)
                    * ($service->pivot->quantity ?? 1)
                );
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
