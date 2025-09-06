<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserDevice;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDevice extends Model
{
    use HasFactory;

    protected $table = 'user_devices';

    protected $fillable = [
        'user_id',
        'device_id',
        'device_type',
        'device_token',
        'os_version',
        'device_model',
        'device_name',
        'device_brand'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
