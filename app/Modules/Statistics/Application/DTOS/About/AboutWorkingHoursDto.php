<?php

namespace App\Modules\Statistics\Application\DTOS\About;

use App\Modules\Base\Application\DTOS\Base\BaseDTO;
use App\Modules\Base\Domain\DTO\BaseDTOAbstract;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;

class AboutWorkingHoursDto extends BaseDTOAbstract
{
    protected array $working_hours;
    public $about_id;
    public $from_time;
    public $to_time;
    public $days = [];

        public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
