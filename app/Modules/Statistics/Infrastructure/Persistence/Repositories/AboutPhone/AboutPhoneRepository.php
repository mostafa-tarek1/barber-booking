<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutPhone;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutPhone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class AboutPhoneRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new AboutPhone());
    }

    public function deleteByAboutId($about_id)
    {
        try {
            $this->getModel()->where('about_id', $about_id)->delete();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
