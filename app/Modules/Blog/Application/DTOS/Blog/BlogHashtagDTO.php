<?php
namespace App\Modules\Blog\Application\DTOS\Blog;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;


class BlogHashtagDTO extends BaseDTOAbstract
{
    public int $hashtag_id;
    public int $blog_id;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
