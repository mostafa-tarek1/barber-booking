<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutUs;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUs\AboutUs;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUsStatistics\AboutUsStatistic;

class AboutUsRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new AboutUs());
    }

    public function deleteAboutUsStatistics(): int
    {
        $ids = AboutUsStatistic::pluck('id')->toArray();
        return AboutUsStatistic::destroy($ids);
    }
}
