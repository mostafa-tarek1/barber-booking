<?php

namespace App\Modules\Notification\Infrastructure\Persistence\Models\Notification;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Notification extends Model
{
  use HasFactory;

  protected $table = 'notifications';

  protected $fillable = [
    'title',
    'message',
    'type',
    'data',
    'read_at',
    'user_id',
    'employee_id',
    'created_by',
    'updated_by'
  ];

  protected $casts = [
    'data' => 'array',
    'read_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * علاقة مع المستخدم
   */
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * علاقة مع الموظف
   */
  public function employee()
  {
    return $this->belongsTo(Employee::class, 'employee_id');
  }

  /**
   * علاقة مع من أنشأ الإشعار
   */
  public function createdBy()
  {
    return $this->belongsTo(Employee::class, 'created_by');
  }

  /**
   * علاقة مع من عدل الإشعار
   */
  public function updatedBy()
  {
    return $this->belongsTo(Employee::class, 'updated_by');
  }

  /**
   * تحديد ما إذا كان الإشعار مقروء
   */
  public function isRead(): Attribute
  {
    return Attribute::make(
      get: fn() => !is_null($this->read_at)
    );
  }

  /**
   * تحديد ما إذا كان الإشعار للموظف
   */
  public function isForEmployee(): Attribute
  {
    return Attribute::make(
      get: fn() => !is_null($this->employee_id)
    );
  }

  /**
   * تحديد ما إذا كان الإشعار للمستخدم
   */
  public function isForUser(): Attribute
  {
    return Attribute::make(
      get: fn() => !is_null($this->user_id)
    );
  }

  /**
   * تحديد نوع المستلم
   */
  public function recipientType(): Attribute
  {
    return Attribute::make(
      get: function () {
        if ($this->employee_id) {
          return 'employee';
        } elseif ($this->user_id) {
          return 'user';
        }
        return null;
      }
    );
  }

  /**
   * تحديد اسم المستلم
   */
  public function recipientName(): Attribute
  {
    return Attribute::make(
      get: function () {
        if ($this->employee_id && $this->employee) {
          return trim($this->employee->first_name . ' ' . $this->employee->last_name);
        } elseif ($this->user_id && $this->user) {
          return $this->user->name ?? $this->user->email;
        }
        return 'غير محدد';
      }
    );
  }

  /**
   * تحديد أيقونة الإشعار حسب النوع
   */
  public function icon(): Attribute
  {
    return Attribute::make(
      get: function () {
        return match ($this->type) {
          'booking_created' => 'calendar-plus',
          'booking_confirmed' => 'calendar-check',
          'booking_cancelled' => 'calendar-x',
          'booking_finished' => 'calendar-check-2',
          'booking_reminder' => 'bell',
          'service_added' => 'plus-circle',
          'service_updated' => 'edit-circle',
          'employee_added' => 'user-plus',
          'system_alert' => 'alert-triangle',
          'payment_received' => 'credit-card',
          'review_received' => 'star',
          default => 'bell'
        };
      }
    );
  }

  /**
   * تحديد لون الإشعار حسب النوع
   */
  public function color(): Attribute
  {
    return Attribute::make(
      get: function () {
        return match ($this->type) {
          'booking_created' => 'blue',
          'booking_confirmed' => 'green',
          'booking_cancelled' => 'red',
          'booking_finished' => 'purple',
          'booking_reminder' => 'yellow',
          'service_added' => 'green',
          'service_updated' => 'blue',
          'employee_added' => 'green',
          'system_alert' => 'red',
          'payment_received' => 'green',
          'review_received' => 'yellow',
          default => 'gray'
        };
      }
    );
  }

  /**
   * Scope للإشعارات غير المقروءة
   */
  public function scopeUnread($query)
  {
    return $query->whereNull('read_at');
  }

  /**
   * Scope للإشعارات المقروءة
   */
  public function scopeRead($query)
  {
    return $query->whereNotNull('read_at');
  }

  /**
   * Scope للإشعارات الموجهة لموظف
   */
  public function scopeForEmployee($query, $employeeId)
  {
    return $query->where('employee_id', $employeeId);
  }

  /**
   * Scope للإشعارات الموجهة لمستخدم
   */
  public function scopeForUser($query, $userId)
  {
    return $query->where('user_id', $userId);
  }

  /**
   * Scope للإشعارات حسب النوع
   */
  public function scopeOfType($query, $type)
  {
    return $query->where('type', $type);
  }

  /**
   * تحديد الإشعار كمقروء
   */
  public function markAsRead()
  {
    $this->update(['read_at' => now()]);
  }

  /**
   * تحديد الإشعار كغير مقروء
   */
  public function markAsUnread()
  {
    $this->update(['read_at' => null]);
  }
}
