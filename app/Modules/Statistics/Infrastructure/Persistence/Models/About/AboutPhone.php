<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutPhone extends Model
{
    use HasFactory;
    protected $guarded = ["id", "created_at", "updated_at"];

   protected $table = 'about_phones';

    public function about() : BelongsTo
    {
        return $this->belongsTo(About::class);
    }
}
