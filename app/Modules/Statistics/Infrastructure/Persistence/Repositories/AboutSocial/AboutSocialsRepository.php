<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutSocial;

use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutPhone;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutSocial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class AboutSocialsRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new AboutSocial());
    }


    public function deleteByAboutId($about_id)
    {
        try {
            $this->getModel()->where('about_id', $about_id)->delete();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteByAboutIdExceptIds($aboutId, $ids = [])
    {
        $query = $this->getModel()->where('about_id', $aboutId);
        if (!empty($ids)) {
            $query->whereNotIn('id', $ids);
        }
        $query->delete();
    }
}
