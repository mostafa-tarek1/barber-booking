<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopServiceResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this['id'],
      'name' => $this['name'],
      'price' => $this['price'],
      'bookings_count' => $this['bookings_count'],
      'total_revenue' => $this['total_revenue'],
    ];
  }
}
