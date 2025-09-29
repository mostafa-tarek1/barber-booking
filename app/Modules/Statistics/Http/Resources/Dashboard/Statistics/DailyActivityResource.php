<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyActivityResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'date' => $this['date'],
      'bookings' => [
        'total' => $this['bookings']['total'],
        'confirmed' => $this['bookings']['confirmed'],
        'cancelled' => $this['bookings']['cancelled'],
        'finished' => $this['bookings']['finished'],
      ],
      'revenue' => $this['revenue'],
    ];
  }
}
