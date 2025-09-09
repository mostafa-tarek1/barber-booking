<?php

namespace App\Modules\Statistics\Application\DTOS\About;

use App\Modules\Base\Application\DTOS\Base\BaseDTO;
use App\Modules\Base\Domain\DTO\BaseDTOAbstract;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;

class AboutSocialsDto extends BaseDTOAbstract
{
    protected array $socials;
    public $about_id;
    public $id;
    public ?string $key;
    public ?string $value;
    public $image;
    public string $imageFolder= "about_socials";
        public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function uniqueAttributes(): array
    {
        return [
            'key'
        ];
    }
}
