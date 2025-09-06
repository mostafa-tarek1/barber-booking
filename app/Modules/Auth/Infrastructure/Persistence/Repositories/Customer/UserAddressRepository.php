<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserAddress\UserAddress;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserAddressRepository extends BaseRepositoryAbstract
{

    public function __construct()
    {
        $this->setModel(new UserAddress());
    }

    public function deleteByUserIdExceptIds($userId, $ids = [])
    {
        Log::info("Deleting non-master addresses start ", [
            'user_id' => $userId,
            'sent_ids' => $ids,
            'existing_non_master' => $this->getModel()
                ->where('user_id', $userId)
                ->where('is_master', false)

                ->whereNotIn('id', $ids)
                ->get()
                ->toArray(),
        ]);
        // $query = $this->getModel()->where('user_id', $userId)->nonMaster();
        $query = $this->getModel()
            ->where('user_id', $userId)
            ->where('is_master', false);//->whereNull('deleted_at');

        // if (!empty($ids)) {
        //     $query->whereNotIn('id', $ids);
        // }


        $query->delete();
        Log::info("Deleting non-master addresses end ", [
            'user_id' => $userId,
            'sent_ids' => $ids,
            'query_sql' => $query->toSql(),
            'query_bindings' => $query->getBindings()
        ]);
    }

    
}
