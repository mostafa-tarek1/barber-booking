<?php

namespace App\Modules\Blog\Application\DTOS\Category;


use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class CategoryDTO extends BaseDTOAbstract
{
    public $category_id;
    public $organization_id;
    public $translations;
    public $image;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
