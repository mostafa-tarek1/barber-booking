<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\ContactUs;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\ContactUs\ContactUs;

class ContactUsRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new ContactUs());
    }
}
