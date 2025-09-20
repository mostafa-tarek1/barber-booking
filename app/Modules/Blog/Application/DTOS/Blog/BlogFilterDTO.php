<?php

namespace App\Modules\Blog\Application\DTOS\Blog;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BlogFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $blog_id;
    public $title;
    public $word;
    public $parent_id;
    public $category_id;
    public $hashtag_id;
    public $limit;
    public $paginate;
    public $created_by;
    public $slug;
    public $description;
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
            'category_id',
            'hashtag_id',
        ];
    }
}
