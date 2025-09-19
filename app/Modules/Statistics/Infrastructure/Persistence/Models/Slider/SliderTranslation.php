<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\Slider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SliderTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'slider_translations';
    protected $fillable = ['title', "description", 'sub_title', 'locale'];
}
