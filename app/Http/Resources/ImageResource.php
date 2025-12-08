<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'image'     => null,
            'is_cover'  => $this->is_cover,
        ];
    }
}
