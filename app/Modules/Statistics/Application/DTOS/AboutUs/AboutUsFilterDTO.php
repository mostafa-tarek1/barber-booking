<?php

namespace App\Modules\Statistics\Application\DTOS\AboutUs;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class AboutUsFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $about_us_id;
    public $title;
    public $description;
    public $word;
    protected $main_image;
    public $parent_id;
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
