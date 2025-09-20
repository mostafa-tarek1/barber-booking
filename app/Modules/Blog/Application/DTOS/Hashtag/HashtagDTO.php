<?php

namespace App\Modules\Blog\Application\DTOS\Hashtag;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class HashtagDTO extends BaseDTOAbstract
{
    public $hashtag_id;
    public $translations;
    public $parent_id;
    public $created_by;
    public $updated_by;
    public $slug;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
