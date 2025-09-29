<?php

namespace App\Modules\Statistics\Application\DTOS\Statistics;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class MonthlyActivityDTO extends BaseDTOAbstract
{
  public $month;
  public $daily_bookings;
  public $total_revenue;

  public function __construct(array $data = [])
  {
    parent::__construct($data);
  }

  public function uniqueAttributes(): array
  {
    return ['month'];
  }
}
