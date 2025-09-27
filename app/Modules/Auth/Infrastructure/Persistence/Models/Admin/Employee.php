<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Admin;

use App\Modules\Auth\Domain\Entity\AuthEmployeeEntity;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\EmployeeDevice\EmployeeDevice;
use App\Modules\Auth\Infrastructure\Persistence\Models\Permission\Permission;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use App\Modules\Base\Domain\Entity\AuthEntityAbstract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Modules\Base\Domain\Holders\AuthHolderInterface;
use App\Modules\Base\Domain\Services\Email\EmailNotification;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Admin\EmployeeRepository;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use App\Modules\Blog\Infrastructure\Persistence\Models\Category\Category;
use App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag\Hashtag;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Rooms\Infrastructure\Persistence\Models\Room\Room;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomLocation\RoomLocation;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Illuminate\Database\Eloquent\Casts\Attribute;


class Employee extends Authenticatable implements AuthHolderInterface
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'employees';

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'phone',
        'email',
        'password',
        'avatar',
        'id_type',
        'id_image',
        'status',
        'is_email_verified',
        'is_phone_verified',
        'email_verified_at',
        'phone_verified_at',
        'is_blocked',
        'employee_type',
        'image',
        'cover',
        'location_id',
        'wallet',
        'last_login',
        'last_os',
        'identify_number',
        'gender',
        'fcm_token',
        'parent_id',
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

    protected function imageLink(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getAttribute('image') ? asset("storage/{$this->getAttribute('image')}") : null
        );
    }
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function employeeDevices()
    {
        return $this->hasMany(EmployeeDevice::class, 'employee_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'employee_id');
    }

    public function getAuthEntity(): AuthEmployeeEntity
    {
        return new AuthEmployeeEntity(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            phone: $this->phone,
            password: $this->password,
            status: $this->status,
            model: $this,
        );
    }

    public function parent()
    {
        return $this->belongsTo(Employee::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Employee::class, 'parent_id');
    }

    public function createdHashtags()
    {
        return $this->hasMany(Hashtag::class, 'created_by');
    }
    public function createdCategories()
    {
        return $this->hasMany(Category::class, 'created_by');
    }

    public function createBlogs()
    {
        return $this->hasMany(Blog::class, 'created_by');
    }

    public function createdBookings()
    {
        return $this->hasMany(
            Booking::class,
            'created_by'
        );
    }

    public function updatedBookings()
    {
        return $this->hasMany(
            Booking::class,
            'updated_by'
        );
    }

    public function bookingServices()
    {
        return $this->belongsToMany(
            Service::class,
            'booking_service',
            'employee_id',
            'service_id'
        )->withPivot('booking_id')
            ->withTimestamps();
    }


    /**
     * Send OTP to this user via email or WhatsApp.
     *
     * @param string $fieldType
     * @param UserRepository $userRepository
     * @param string|null $otp
     * @return void
     */
    // public function sendOtp(string $fieldType, EmployeeRepository $employeeRepository, ?string $otp = null): void
    // {
    //     $otp = $otp ?? random_int(100000, 999999); // Or generate a dynamic one
    //     setCache("otp_{$this->$fieldType}", $otp, now()->addMinutes(5));

    //     if (checkCredentialEmail($this->$fieldType)) {
    //         $this->notify(new EmailNotification($otp));
    //     } else {
    //         $employeeRepository->sendWhatsAppMessage($this->phone, "Your OTP is: $otp");
    //     }
    // }
}
