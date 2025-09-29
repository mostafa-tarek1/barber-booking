<?php

namespace App\Modules\Statistics\Application\DTOS\Statistics;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class StatisticsDTO extends BaseDTOAbstract
{
    public $total_bookings;
    public $total_revenue;
    public $total_employees;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function uniqueAttributes(): array
    {
        return [];
    }
}
