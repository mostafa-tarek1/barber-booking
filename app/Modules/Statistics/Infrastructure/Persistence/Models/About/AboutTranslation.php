<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'about_translations';
    protected $fillable = ['title', 'description' , 'location'];
}
