<?php

namespace App\Modules\Statistics\Application\DTOS\AboutUs;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class AboutUsStatisticsFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $about_us_id;
    public $key;
    public $value;
    public $icon;
    public $word;
    protected $main_image;
    public $parent_id;
    public $status;
    public $limit;
    public $paginate;
    public $created_by;
    public $about_us_statistics_id;


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
