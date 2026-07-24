<?php

namespace App\Modules\Service\Infrastructure\Persistence\Models\Package;

use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Package extends Model implements TranslatableContract
{
    use Translatable;

    public array $translatedAttributes = ['name', 'description'];

    protected $fillable = ['price', 'image', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_link', 'duration'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'package_service')
            ->withPivot(['quantity', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function getImageLinkAttribute(): ?string
    {
        return $this->image && Storage::disk('public')->exists($this->image)
            ? asset('storage/'.$this->image)
            : null;
    }

    public function getDurationAttribute(): int
    {
        return (int) $this->services->sum(
            fn (Service $service) => $service->duration * $service->pivot->quantity
        );
    }
}
