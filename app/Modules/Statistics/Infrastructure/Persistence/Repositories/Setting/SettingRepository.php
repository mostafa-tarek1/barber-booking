<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\Setting;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\Setting\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class SettingRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new Setting());
    }
}
