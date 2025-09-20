<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\Term;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\Term\Term;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class TermRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new Term());
    }
}
