<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUsImage;

use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUs\AboutUs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class AboutUsImage extends Model
{
    use HasFactory;
    protected $table = 'about_us_images';
    protected $fillable = [
        'about_us_id',
        'file',
        'alt',
    ];
    public function aboutUs()
    {
        return $this->belongsTo(AboutUs::class);
    }

    public function fileLink(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->file && Storage::disk('public')->exists($this->file)) {
                    return asset('storage/' . $this->file);
                }
                return "";
            }
        );
    }
}






