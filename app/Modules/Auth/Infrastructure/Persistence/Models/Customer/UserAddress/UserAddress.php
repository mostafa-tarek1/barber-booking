<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserAddress;

use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory;
        // use SoftDeletes;


    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id',
        'address',
        'lat',
        'lng',
        'file_address',
        'is_master'
    ];


    protected $casts = [
        'is_master' => 'boolean',
    ];

    public function scopeNonMaster($query)
    {
        return $query->where('is_master', false);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fileLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->file_address && Storage::disk('public')->exists($this->file_address)) {
                    return asset('storage/' . $this->file_address);
                }
                return "";
            }
        );
    }
}
