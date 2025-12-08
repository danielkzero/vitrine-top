<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'subtitle'  => $this->subtitle,
            'image'     => $this->image_base64,
            'order'     => $this->order,
            'is_active' => $this->is_active,
        ];
    }
}
