<?php

namespace App\Modules\Blog\Application\DTOS\Blog;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BlogDTO extends BaseDTOAbstract
{
    public $blog_id;
    public $translations;
    public $parent_id;
    public $image;
    public $created_by;
    public $updated_by;
    public $slug;
    public ?array $hashtag_ids = [];
    public ?array $category_ids = [];
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
