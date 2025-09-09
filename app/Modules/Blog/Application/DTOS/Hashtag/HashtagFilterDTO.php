<?php

namespace App\Modules\Blog\Application\DTOS\Hashtag;


use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

class HashtagFilterDTO extends BaseDTOAbstract
{
    public $hashtag_id;
    public $organization_id;
    public $translations;
    public $image;
    public $slug;
    public $title;
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
