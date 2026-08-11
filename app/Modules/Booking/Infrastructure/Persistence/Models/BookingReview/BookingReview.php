<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Models\BookingReview;

use App\Modules\Booking\Domain\Enums\BookingReviewStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Illuminate\Database\Eloquent\Model;

class BookingReview extends Model
{
    protected $fillable = [
        'booking_id',
        'name',
        'phone',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => BookingReviewStatusEnum::class,
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function publicName(): string
    {
        return preg_split('/\s+/u', trim($this->name))[0];
    }
}
