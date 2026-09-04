<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\Concerns\ResolvesStore;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    use ResolvesStore;

    public function index(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $favorites = Favorite::query()
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->with('product')
            ->latest()
            ->get();

        return response()->json(['data' => $favorites]);
    }

    public function store(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query
                    ->where('user_id', $store->id)
                    ->where('is_public', true)),
            ],
        ]);

        $favorite = Favorite::query()->firstOrCreate([
            'user_id' => $store->id,
            'customer_id' => $customer->id,
            'product_id' => $data['product_id'],
        ]);

        return response()->json(['data' => $favorite], 201);
    }

    public function destroy(Request $request, string $storeSlug, int $productId): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        Favorite::query()
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json(['message' => 'Favorito removido.']);
    }
}
