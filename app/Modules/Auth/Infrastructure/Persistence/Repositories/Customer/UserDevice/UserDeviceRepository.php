<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserDevice;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserDevice\UserDevice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Modules\Base\Domain\ApiService\WhatsAppApiService;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\User\Infrastructure\Persistence\Models\User\User;
use App\Modules\Base\Domain\Services\Notification\NotificationApiService;
use App\Modules\User\Infrastructure\Persistence\ApiService\StageApiService;
use App\Modules\User\Infrastructure\Persistence\ApiService\LocationApiService;

class UserDeviceRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new UserDevice());
    }
}
