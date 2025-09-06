<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Customer;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Auth\Domain\Entity\AuthUserEntity;
use App\Modules\Auth\Infrastructure\Persistence\Entities\UserEntity;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\PhoneRequest\PhoneRequest;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserAddress\UserAddress;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\UserDevice\UserDevice;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Customer\UserRepository;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Modules\Base\Domain\Holders\AuthHolderInterface;
use App\Modules\Base\Domain\Services\Email\EmailNotification;
use App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification;
use App\Modules\Order\Infrastructure\Persistence\Models\Order\Order;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LDAP\Result;

class User extends Authenticatable implements AuthHolderInterface
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens/* , SoftDeletes */;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'users';


    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'username',
        'phone',
        'email',
        'password',
        'image',
        'id_number',
        'id_type',
        'id_image',
        'status',
        'is_email_verified',
        'is_phone_verified',
        'email_verified_at',
        'phone_verified_at',
        'organization_id',
        'stage_id',
        'location_id',
        'nationality_id',
        'wallet',
        'last_login',
        'last_os',
        'phone_code',
        'cover',
        'commercial_register',
        'tax_register',
        'is_blocked',
        'is_approved',
        'is_online',
        'is_verified',
        'allow_notification'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $user_entity;

    public function getEntity(): UserEntity
    {
        if (!$this->user_entity) {
            $this->user_entity = new UserEntity($this);
        }
        return $this->user_entity;
    }
    public function getImageLinkAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function coverLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->cover && Storage::disk('public')->exists($this->cover)) {
                    return asset('storage/' . $this->cover);
                }
                return "";
            }
        );
    }

    public function commercialRegisterLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->commercial_register && Storage::disk('public')->exists($this->commercial_register)) {
                    return asset('storage/' . $this->commercial_register);
                }
                return "";
            }
        );
    }

    public function taxRegisterLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->tax_register && Storage::disk('public')->exists($this->tax_register)) {
                    return asset('storage/' . $this->tax_register);
                }
                return "";
            }
        );
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function userDevices()
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }

    public function getAuthEntity(): AuthUserEntity
    {
        return new AuthUserEntity(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            phone: $this->phone,
            password: $this->password,
            status: $this->status,
            model: $this,
        );
    }

    /**
     * Send OTP to this user via email or WhatsApp.
     *
     * @param string $fieldType
     * @param UserRepository $userRepository
     * @param string|null $otp
     * @return void
     */
    public function sendOtp(string $fieldType, UserRepository $userRepository, ?string $otp = null): void
    {
        $otp = $otp ?? random_int(100000, 999999);
        setCache("otp_{$this->$fieldType}", $otp, now()->addMinutes(5));

        if (checkCredentialEmail($this->$fieldType)) {
            $this->notify(new EmailNotification($otp));
        } else {
            $userRepository->sendWhatsAppMessage($this->phone, $this->phone_code, "Your OTP is: $otp");
        }
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_users', 'user_id', 'notification_id')->withPivot('is_read');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->wherePivot('is_read', false);
    }
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function orders(): HasMany{
        return $this->hasMany(Order::class, 'user_id');
    }

    public function phoneRequests():HasMany
    {
        return $this->hasMany(PhoneRequest::class, 'user_id');
    }
}
