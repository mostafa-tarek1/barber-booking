<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Models\Permission;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $fillable = [
        'permission',
        'employee_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
