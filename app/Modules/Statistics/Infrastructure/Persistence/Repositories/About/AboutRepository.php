<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\About;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\About;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class AboutRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new About());
    }
}
