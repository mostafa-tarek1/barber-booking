<?php

namespace App\Modules\Statistics\Application\DTOS\Statistics;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class TopServiceDTO extends BaseDTOAbstract
{
    public $id;
    public $name;
    public $price;
    public $bookings_count;
    public $total_revenue;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function uniqueAttributes(): array
    {
        return ['id'];
    }
}
