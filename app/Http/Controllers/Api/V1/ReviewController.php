<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Repositories\UserRepository;

class ReviewController extends Controller
{
    public function index(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $reviews = $user
            ->reviews()
            ->approved()
            ->latest()
            ->get();

        return ReviewResource::collection($reviews);
    }

    public function product(string $slug, int $id)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $reviews = $user
            ->reviews()
            ->where('product_id', $id)
            ->approved()
            ->latest()
            ->get();

        return ReviewResource::collection($reviews);
    }
}
