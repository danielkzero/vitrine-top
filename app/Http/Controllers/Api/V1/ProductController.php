<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Repositories\UserRepository;

class ProductController extends Controller
{
    public function index(string $slug)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $products = $user->products()
            ->where('is_public', true)
            ->with(['images', 'category'])
            ->get();

        return ProductResource::collection($products);
    }

    public function show(string $slug, int $id)
    {
        $user = (new UserRepository)->findBySlug($slug);

        $product = $user->products()
            ->where('id', $id)
            ->where('is_public', true)
            ->with(['images', 'category', 'reviews' => fn ($q) => $q->approved()])
            ->firstOrFail();

        return new ProductResource($product);
    }
}
