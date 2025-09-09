<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;




class BlogRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Blog());
    }

}
