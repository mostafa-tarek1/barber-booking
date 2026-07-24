<?php

namespace App\Modules\Service\Infrastructure\Persistence\Models\Package;

use Illuminate\Database\Eloquent\Model;

class PackageTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = ['name', 'description'];
}
