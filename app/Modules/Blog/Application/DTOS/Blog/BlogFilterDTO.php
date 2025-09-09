<?php

namespace App\Modules\Blog\Application\DTOS\Blog;


use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class BlogFilterDTO extends BaseDTOAbstract
{
    public $blog_id;
    public $organization_id;
    public $translations;
    public $image;
    public $slug;
    public $hashtags;
    public $categories;
    public $title;
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

    public function whereHasRelations(): array
    {
        $conditions = [];

        if (!empty($this->hashtags)) {
            $conditions['hashtags'] = ['hashtags.id' => $this->hashtags];
        }

        if (!empty($this->categories)) {
            $conditions['categories'] = ['categories.id' => $this->categories];
        }

        return $conditions;
    }

}
