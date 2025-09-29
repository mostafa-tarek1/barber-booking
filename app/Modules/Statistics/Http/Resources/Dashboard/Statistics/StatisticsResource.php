<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_bookings' => $this->total_bookings,
            'total_revenue' => $this->total_revenue,
            'total_employees' => $this->total_employees,
        ];
    }
}
