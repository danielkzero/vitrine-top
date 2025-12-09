<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'rating'        => $this->rating,
            'comment'       => $this->comment,
            'customer_name' => $this->customer_name,
            'whatsapp'      => $this->whatsapp,
            'created_at'    => $this->created_at,
        ];
    }
}
