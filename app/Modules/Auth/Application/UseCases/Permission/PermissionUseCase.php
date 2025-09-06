<?php
namespace App\Modules\Auth\Application\UseCases\Permission;

use App\Modules\Auth\Application\DTOS\Permission\PermissionDTO;
use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Admin\EmployeeRepository;
use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Permission\PermissionRepository;

class PermissionUseCase
{
    protected PermissionRepository $permissionRepository;
    protected EmployeeRepository $employeeRepository;

    public function __construct()
    {
        $this->permissionRepository = new PermissionRepository();
        $this->employeeRepository = new EmployeeRepository();
    }
    /**
     * Update or create permission
     *
     * @param mixed $dto
     * @return DataSuccess
     */

    public function updateOrCreate(PermissionDTO $dto): DataSuccess
    {
        // dd($dto->employee_id);
        $employee = $this->employeeRepository->getById($dto->employee_id);

        $permission = $this->permissionRepository->updateOrCreateMultiple($dto);
        return new DataSuccess(
            data: new AdminResource($employee),
            message: 'Permission updated successfully'
        );
    }

    public function delete(PermissionDTO $dto): DataSuccess
    {
        $permission = $this->permissionRepository->delete($dto->id);
        return new DataSuccess(
            data: new AdminResource($permission),
            message: 'Permission deleted successfully'
        );
    }



    public function fetch($dto): DataSuccess
    {
        $permission = $this->permissionRepository->getFirst();
        return new DataSuccess(
            data: new AdminResource($permission),
            message: 'Permission fetched successfully'
        );
    }
}
