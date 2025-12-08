<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'surname'           => $this->surname,
            'business_name'     => $this->business_name,
            'slug'              => $this->slug,
            'avatar'            => $this->avatar,
            'logo_path'         => $this->logo_path,
            'subtitle'          => $this->subtitle,
            'theme_color'       => $this->theme_color,
            'is_active'         => $this->is_active,
            'background_path'   => $this->background_path
        ];
    }
}
