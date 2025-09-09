<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\Slider;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\Slider\Slider;

class SliderRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Slider());
    }
}
