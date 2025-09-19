<?php

namespace App\Modules\Statistics\Application\DTOS\AboutUs;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class AboutUsDTO extends BaseDTOAbstract
{
    public  $title_en;
    public  $title_ar;
    public  $description_en;
    public  $description_ar;
    public $sub_title_en;
    public $sub_title_ar;
    public $our_mission_en;
    public $our_mission_ar;
    public $our_goal_en;
    public $our_goal_ar;
    public $our_vision_en;
    public $our_vision_ar;
    public  $about_us_id;
    public  $main_image;
    public $key;
    public $value;
    public $icon;
    public $statistics;
    public $images;
    public $files;
    public $alt;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
