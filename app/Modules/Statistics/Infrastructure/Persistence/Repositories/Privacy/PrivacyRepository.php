<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\Privacy;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\Privacy\Privacy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class PrivacyRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new Privacy());
    }
}
