<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Customer\PhoneRequest;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PhoneRequest extends Model
{
    use HasFactory;
    protected $table = 'phone_requests';
    protected $fillable = [
        'user_id',
        'phone',
        'phone_code',
        'is_verified',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
