<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\ActivityLog;

use App\Modules\Auth\Application\DTOS\ActivityLog\ActivityLogFilterDTO;
use App\Modules\Auth\Application\UseCases\ActivityLog\FetchActivityLogsUseCase;
use App\Modules\Auth\Http\Requests\Dashboard\ActivityLog\FilterActivityLogRequest;
use App\Modules\Auth\Http\Resources\ActivityLog\ActivityLogResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityLogController
{
    public function index(
        FilterActivityLogRequest $request,
        FetchActivityLogsUseCase $useCase
    ): AnonymousResourceCollection {
        $dto = ActivityLogFilterDTO::fromRequest($request);

        $logs = $useCase->handle($dto);

        return ActivityLogResource::collection($logs);
    }
}
