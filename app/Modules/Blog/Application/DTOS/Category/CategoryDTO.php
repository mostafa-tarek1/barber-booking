<?php

namespace App\Modules\Blog\Application\DTOS\Category;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class CategoryDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $category_id;
    public $translations;
    public $parent_id;
    public $image;
    public $created_by;
    public $updated_by;
    public $is_active = true;
    public $slug;
    protected string $imageFolder = 'categories'; // fallback folder
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }


}
