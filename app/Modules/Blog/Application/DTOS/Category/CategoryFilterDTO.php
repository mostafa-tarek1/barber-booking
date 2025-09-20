<?php

namespace App\Modules\Blog\Application\DTOS\Category;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class CategoryFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $category_id;
    public $title;
    public $word;
    public $parent_id;
    public $status;
    public $limit;
    public $paginate;
    public $created_by;
    public  $slug;
    public $is_parent=true;
    public $is_active;
    public $duration_id;
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
            'word',
            'is_parent'
        ]; // Default empty array
    }
}
