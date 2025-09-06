<?php
namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\ActivityLog;
use App\Modules\Auth\Application\DTOS\ActivityLog\ActivityLogFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;
interface ActivityLogRepositoryInterface
{
    public function fetch(ActivityLogFilterDTO $dto): LengthAwarePaginator;
}
