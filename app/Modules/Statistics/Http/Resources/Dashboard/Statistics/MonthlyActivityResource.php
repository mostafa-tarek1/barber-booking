<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyActivityResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'month' => $this['month'],
      'daily_bookings' => $this['daily_bookings'],
      'total_revenue' => $this['total_revenue'],
    ];
  }
}
