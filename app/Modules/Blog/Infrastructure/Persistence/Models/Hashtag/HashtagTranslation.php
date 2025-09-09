<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Models\Hashtag;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HashtagTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'hashtag_translations';
    protected $fillable = [
        'hashtag_id',
        'locale',
        'title',
    ];
}
