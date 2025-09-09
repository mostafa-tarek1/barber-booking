<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\OurPartner;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\Faq\Faq;
use App\Modules\Statistics\Infrastructure\Persistence\Models\OurPartner\OurPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class OurPartnerRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new OurPartner());
    }
}
