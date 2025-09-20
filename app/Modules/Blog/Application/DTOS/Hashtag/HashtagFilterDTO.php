<?php

namespace App\Modules\Blog\Application\DTOS\Hashtag;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class HashtagFilterDTO extends BaseDTOAbstract
{
    protected bool $excludeAttributes = true;
    public $hashtag_id;
    public $title;
    public $word;
    public $parent_id;
    public $limit;
    public $paginate;
    public $created_by;
    public  $slug;
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
            'word'
        ]; // Default empty array
    }
}
