<?php

namespace App\Modules\Statistics\Application\DTOS\About;

use App\Modules\Base\Application\DTOS\Base\BaseDTO;
use App\Modules\Base\Domain\DTO\BaseDTOAbstract;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;



class AboutEmailsDto extends BaseDTOAbstract
{
    public $about_id;
    public $email;

        public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

}
