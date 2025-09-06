<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Permission;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Dashboard\Permission\UpdateOrCreatePermissionRequest;
use App\Modules\Auth\Application\UseCases\Permission\PermissionUseCase;
use App\Modules\Statistics\Application\UseCases\Privacy\PrivacyUseCase;
use App\Modules\Statistics\Http\Requests\Dashboard\Privacy\CreateOrUpdatePrivacyRequest;
use App\Modules\Statistics\Http\Requests\Dashboard\Privacy\FetchPrivacyRequest;



class PermissionController extends Controller
{
    protected PermissionUseCase $permissionUseCase;

    public function __construct(PermissionUseCase $permissionUseCase)
    {
        $this->permissionUseCase = $permissionUseCase;
    }

    public function store_permission(UpdateOrCreatePermissionRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->permissionUseCase->updateOrCreate(dto: $data)->response();
    }
    public function fetch_permission(UpdateOrCreatePermissionRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->toDTO();
        return $this->permissionUseCase->fetch(dto: $data)->response();
    }


}
