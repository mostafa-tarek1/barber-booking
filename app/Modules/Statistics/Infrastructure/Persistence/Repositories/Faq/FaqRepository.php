<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\Faq;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\Faq\Faq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class FaqRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new Faq());
    }
}
