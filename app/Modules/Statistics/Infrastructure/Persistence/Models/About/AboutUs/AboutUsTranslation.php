<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUsTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'about_us_translations';
    protected $fillable = ['title', "description", "locale", "sub_title", "our_vision" , "our_goal", "our_mission"];
}
