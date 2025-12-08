<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function index(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $categories = $user->categories()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return CategoryResource::collection($categories);
    }
}
