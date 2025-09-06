<?php
namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\ActivityLog;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\ActivityLog\ActivityLogRepositoryInterface;
use App\Modules\Auth\Application\DTOS\ActivityLog\ActivityLogFilterDTO;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function fetch(ActivityLogFilterDTO $dto): LengthAwarePaginator
    {
        $query = Activity::with(['causer', 'subject'])->latest();
        if ($dto->subject_id) {
            $query->where('subject_id', $dto->subject_id);
        }
        if ($dto->subject_type) {
            $query->where('subject_type', $dto->subject_type);
        }
        return $query->paginate(10);
    }
}
