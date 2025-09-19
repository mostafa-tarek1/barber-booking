<?php

namespace App\Modules\Statistics\Application\DTOS\Slider;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;


class SliderDTO extends BaseDTOAbstract
{
    public  $title_en;
    public  $title_ar;
    public  $description_en;
    public  $description_ar;
    public $sub_title_ar;
    public $sub_title_en;
    public  $slider_id;
    public $image;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
