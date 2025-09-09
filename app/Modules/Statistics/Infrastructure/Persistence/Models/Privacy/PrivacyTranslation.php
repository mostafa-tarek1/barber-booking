<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\Privacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'privacy_translations';
    protected $fillable = ['title', "description", 'subtitle'];
}
