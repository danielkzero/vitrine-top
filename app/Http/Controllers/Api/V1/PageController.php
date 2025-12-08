<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Resources\PageResource;

class PageController extends Controller
{
    public function index(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $pages = $user->pages()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return PageResource::collection($pages);
    }
}
