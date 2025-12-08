<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Resources\BannerResource;

class BannerController extends Controller
{
    public function index(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $banners = $user->banners()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return BannerResource::collection($banners);
    }
}
