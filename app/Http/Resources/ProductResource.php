<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'code'            => $this->code,
            'name'            => $this->name,
            'description'     => $this->description,
            'price'           => $this->price,
            'discount_price'  => $this->discount_price,
            'stock'           => $this->stock,
            'is_public'       => $this->is_public,
            'allow_whatsapp'  => $this->allow_whatsapp,
            'cover_image'     => $this->cover_image,
            'seo_title'       => $this->seo_title,
            'seo_description' => $this->seo_description,
            'rating'          => $this->rating,

            'images'          => ImageResource::collection($this->whenLoaded('images')),
            'category'        => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
