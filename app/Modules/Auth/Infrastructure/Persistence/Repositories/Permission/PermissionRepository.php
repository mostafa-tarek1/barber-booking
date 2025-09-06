<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\Permission;

use App\Modules\Auth\Infrastructure\Persistence\Models\Permission\Permission;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class PermissionRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Permission());
    }

public function updateOrCreateMultiple(BaseDTOInterface|array $dto): Model|Collection
{
    try {
        DB::beginTransaction();

        $dtoList = is_array($dto) ? $dto : [$dto];
        $models = $this->getModel()->newCollection();

        foreach ($dtoList as $singleDto) {
            if (!isset($singleDto->employee_id)) {
                continue;
            }

            $this->getModel()
                ->where('employee_id', $singleDto->employee_id)
                ->delete();

            if (!empty($singleDto->permissions)) {
                foreach ($singleDto->permissions as $permission) {

                    $model = $this->getModel()->create([
                        'employee_id' => $singleDto->employee_id,
                        'permission' => $permission,
                    ]);
                    $models->push($model);
                }
            }
        }


        DB::commit();

        return $models->count() === 1 ? $models->first() : $models;
    } catch (Exception $e) {
        DB::rollBack();

        foreach ($dtoList as $singleDto) {
            deleteFilesFromDto($singleDto);
        }

        report($e);

        return $models->count() === 1
            ? $this->getModel()->newInstance()
            : $this->getModel()->newCollection();
    }
}
}
