<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\BlogCategories;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\BlogHashtags;

class BlogCategoriesRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new BlogCategories());
    }

    public function deleteByBlogId(int $blogId): bool
    {
        return BlogCategories::where('blog_id', $blogId)->delete() > 0;
    }

}
