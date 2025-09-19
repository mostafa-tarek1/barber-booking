<?php

namespace App\Modules\Statistics\Application\DTOS\AboutUs;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class AboutUsImageDTO extends BaseDTOAbstract
{
    public $file;
    public $alt;
    public $icon;
    public $about_us_image_id;
    public $about_us_id;

    public string $imageFolder = 'about_us/images';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
