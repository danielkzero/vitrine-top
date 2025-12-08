<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'theme_color'          => $this->theme_color,
            'text_color'           => $this->text_color,
            'cover_image'          => $this->cover_image_url,
            'profile_image'        => $this->profile_image_url,
            'seo_title'            => $this->seo_title,
            'seo_description'      => $this->seo_description,
            'allow_name_suggestions' => $this->allow_name_suggestions,
        ];
    }
}
