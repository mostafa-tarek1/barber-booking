<?php

namespace App\Modules\Statistics\Http\Resources\Dashboard\About;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutDetailResource extends AboutResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $data = array_merge(parent::toArray($request),[
            "phones" => AboutPhonesResource::collection($this->phones ?? []),
            "emails" => AboutEmailsResource::collection($this->emails ?? []),
            "socials" =>  AboutSocialResource::collection($this->socials ?? [])
        ]);
        return $data;
    }
}
