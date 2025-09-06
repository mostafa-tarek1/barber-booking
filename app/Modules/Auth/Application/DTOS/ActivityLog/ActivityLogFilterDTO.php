<?php

namespace App\Modules\Auth\Application\DTOS\ActivityLog;


use App\Modules\Auth\Http\Requests\Dashboard\ActivityLog\FilterActivityLogRequest;
use App\Modules\Base\Domain\DTO\BaseDTOAbstract;

use App\Modules\Base\Domain\DTO\BaseDTOInterface;

class ActivityLogFilterDTO extends BaseDTOAbstract
{
    // public int $id;
    public $subject_id;
    public $subject_type;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public static function fromRequest(FilterActivityLogRequest $request): self
    {
        return new self([
            // 'id' => $request->input('id'),
            'subject_id' => $request->input('subject_id'),
            'subject_type' => $request->input('subject_type'),
        ]);
    }
}
