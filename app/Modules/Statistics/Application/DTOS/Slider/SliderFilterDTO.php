<?php

namespace App\Modules\Statistics\Application\DTOS\Slider;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class SliderFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public  $title_en;
    public  $title_ar;
    public  $description_en;
    public  $description_ar;
    public $sub_title_ar;
    public $sub_title_en;
    public  $slider_id;
    public $image;
    public $word;
    public $status;
    public $limit;
    public $paginate;
    public $created_by;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function excludedAttributes(): array
    {
        return [
            'excludeAttributes',
            'limit',
            'paginate',
            'word'
        ]; // Default empty array
    }
}
