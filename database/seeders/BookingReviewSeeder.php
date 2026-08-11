<?php

namespace Database\Seeders;

use App\Modules\Booking\Domain\Enums\BookingReviewStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingReview\BookingReview;
use Illuminate\Database\Seeder;

class BookingReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            ['name' => 'أحمد محمد', 'phone' => '01010000001', 'rating' => 5, 'comment' => 'المكان نضيف جداً والتعامل محترم، والحلاقة طلعت زي ما كنت عاوز بالظبط.'],
            ['name' => 'محمود علي', 'phone' => '01010000002', 'rating' => 5, 'comment' => 'تجربة ممتازة والمواعيد مظبوطة، أكيد مش آخر مرة.'],
            ['name' => 'يوسف خالد', 'phone' => '01010000003', 'rating' => 5, 'comment' => 'شغل احترافي واهتمام بالتفاصيل، تسلم إيد التيم كله.'],
            ['name' => 'عمر حسن', 'phone' => '01010000004', 'rating' => 4, 'comment' => 'خدمة جميلة وأجواء مريحة جداً، وأنصح أي حد يجرب المكان.'],
            ['name' => 'محمد سامح', 'phone' => '01010000005', 'rating' => 5, 'comment' => 'من أحسن تجارب الحلاقة اللي جربتها، ذوق ونضافة ونتيجة ممتازة.'],
        ];

        foreach ($reviews as $review) {
            BookingReview::query()->updateOrCreate(
                ['phone' => $review['phone'], 'booking_id' => null],
                [...$review, 'status' => BookingReviewStatusEnum::APPROVED]
            );
        }
    }
}
