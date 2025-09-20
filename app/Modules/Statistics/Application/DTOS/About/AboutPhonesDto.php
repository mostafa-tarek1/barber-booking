<?php

namespace App\Modules\Statistics\Application\DTOS\About;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class AboutPhonesDto extends BaseDTOAbstract
{
    public $about_id;
    public $phone;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
