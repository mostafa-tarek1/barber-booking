<?php

namespace App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUsStatistics;
use App\Modules\Statistics\Infrastructure\Persistence\Models\About\AboutUs\AboutUs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class AboutUsStatistic extends Model
{
    use HasFactory;
    protected $table = 'about_us_statistics';
    protected $fillable = [
        'about_us_id',
        'key',
        'value',
        'icon',
    ];
    public function aboutUs()
    {
        return $this->belongsTo(AboutUs::class);
    }

    public function iconLink(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->icon && Storage::disk('public')->exists($this->icon)) {
                    return asset('storage/' . $this->icon);
                }
                return "";
            }
        );
    }
}
