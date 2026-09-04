<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Api\Concerns\ResolvesStore;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreCatalogController extends Controller
{
    use ResolvesStore;

    public function info(string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);

        return response()->json([
            'id' => $store->id,
            'name' => $store->business_name,
            'slug' => $store->slug,
            'description' => $store->description,
            'whatsapp' => $store->whatsapp,
        ]);
    }

    public function pages(string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);

        $pages = Page::query()
            ->where('user_id', $store->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'key', 'title', 'type', 'cover_image']);

        return response()->json(['data' => $pages]);
    }

    public function page(string $storeSlug, string $pageKey): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);

        $page = Page::query()
            ->where('user_id', $store->id)
            ->where('key', $pageKey)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json(['data' => $page]);
    }

    public function products(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);

        $products = Product::query()
            ->where('user_id', $store->id)
            ->where('is_public', true)
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('id')
            ->paginate(min(50, max(1, (int) $request->integer('per_page', 15))));

        return response()->json($products);
    }

    public function product(string $storeSlug, int $productId): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);

        $product = Product::query()
            ->where('user_id', $store->id)
            ->where('id', $productId)
            ->where('is_public', true)
            ->with('images:id,product_id,image_path,image_base64,is_cover')
            ->firstOrFail();

        return response()->json(['data' => $product]);
    }
}
