<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Repositories\Category;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Blog\Infrastructure\Persistence\Models\Category\Category;




class CategoryRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Category());
    }
}
