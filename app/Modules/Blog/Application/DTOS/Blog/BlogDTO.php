<?php

namespace App\Modules\Blog\Application\DTOS\Blog;


use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BlogDTO extends BaseDTOAbstract
{
    public $blog_id;
    public $organization_id;
    public $translations;
    public $image;
    public $slug;
    public $hashtags;
    public $categories;
    public $author;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function excludedAttributes(): array
    {
        return [
            'hashtags',
            'categories',
        ]; // Default empty array
    }
}
