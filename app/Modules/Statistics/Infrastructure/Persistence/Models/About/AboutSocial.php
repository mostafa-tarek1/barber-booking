<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AboutSocial extends Model
{
    use HasFactory;
    // protected $guarded = ["id", "created_at", "updated_at"];
    protected $fillable = ["about_id", "key", "value", "image", "is_active"];

    protected $table = 'about_socials';
    
    public function imageLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->image && Storage::disk('public')->exists($this->image)) {
                    return asset('storage/' . $this->image);
                }
                return "";
            }
        );
    }
    public function about(): BelongsTo
    {
        return $this->belongsTo(About::class);
    }
}
