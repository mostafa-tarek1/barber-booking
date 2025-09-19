<?php
namespace App\Modules\Blog\Application\DTOS\Blog;

use App\Modules\Base\Domain\DTO\BaseDTOAbstract;


class UpdateBlogHashtagsDTO extends BaseDTOAbstract
{
    public array $hashtag_ids;
    public int $blog_id;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
