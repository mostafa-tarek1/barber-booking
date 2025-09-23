<?php

namespace App\Modules\Service\Infrastructure\Persistence\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'service_translations';
    protected $fillable = ['name','description' ,'locale'];
}
