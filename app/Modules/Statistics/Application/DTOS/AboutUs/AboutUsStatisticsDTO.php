<?php

namespace App\Modules\Statistics\Application\DTOS\AboutUs;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class AboutUsStatisticsDTO extends BaseDTOAbstract
{
    public $key;
    public $value;
    public $icon;
    public $about_us_statistics_id;
    public $about_us_id;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function uniqueAttributes(): array
    {
        return ['about_us_id', 'key'];
    }
}
