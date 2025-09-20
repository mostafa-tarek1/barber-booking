<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Repositories\Hashtag;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag\Hashtag;

class HashtagRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new Hashtag());
    }
}
