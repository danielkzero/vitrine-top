<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'order'         => $this->order,
            'is_active'     => $this->is_active,
            'days_of_week'  => $this->days_of_week,
        ];
    }
}
