<?php

namespace App\Modules\Statistics\Http\Resources\Api\AboutEmail;


use App\Modules\Course\Http\Resources\CourseSetting\SettingResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Course\Http\Resources\Video\VideoResource;
use App\Modules\Course\Http\Resources\CoursePayment\CoursePaymentResource;

class AboutEmailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email, // Assuming 'email' is the field you want to return
        ];
    }
}
