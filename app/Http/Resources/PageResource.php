<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'key'             => $this->key,
            'title'           => $this->title,
            'icon'            => $this->icon,
            'type'            => $this->type,
            'is_active'       => $this->is_active,
            'order'           => $this->order,
            'content'         => $this->content,
            'cover_image'     => $this->cover_image,
            'seo_title'       => $this->seo_title,
            'seo_description' => $this->seo_description,
            'excerpt'         => $this->excerpt,
        ];
    }
}
