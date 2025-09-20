<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\BlogHashtags;

class BlogHashtagsRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new BlogHashtags());
    }

    public function deleteByBlogId(int $blogId): bool
    {
        return BlogHashtags::where('blog_id', $blogId)->delete() > 0;
    }

}
