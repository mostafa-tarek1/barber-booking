<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutUsStatistics;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUsStatistics\AboutUsStatistic;

class AboutUsStatisticRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new AboutUsStatistic());
    }
}
