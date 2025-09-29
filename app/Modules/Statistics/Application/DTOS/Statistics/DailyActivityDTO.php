<?php

namespace App\Modules\Statistics\Application\DTOS\Statistics;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class DailyActivityDTO extends BaseDTOAbstract
{
  public $date;
  public $bookings;
  public $revenue;

  public function __construct(array $data = [])
  {
    parent::__construct($data);
  }

  public function uniqueAttributes(): array
  {
    return ['date'];
  }
}
