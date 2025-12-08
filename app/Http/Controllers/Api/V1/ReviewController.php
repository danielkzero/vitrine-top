<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function index(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $reviews = $user->reviews()
            ->approved()
            ->latest()
            ->get();

        return ReviewResource::collection($reviews);
    }
}
