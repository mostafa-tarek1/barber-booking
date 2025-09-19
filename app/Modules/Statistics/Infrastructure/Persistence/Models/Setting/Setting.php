<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\Setting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings';
    protected $fillable = [
        'tax_amount',
        'tax_type',
    ];
}
