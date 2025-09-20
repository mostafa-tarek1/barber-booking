<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutUsImage;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUsImage\AboutUsImage;

class AboutUsImageRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new AboutUsImage());
    }
}
