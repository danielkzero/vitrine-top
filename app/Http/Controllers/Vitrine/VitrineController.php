<?php

namespace App\Http\Controllers\Vitrine;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VitrineController extends Controller
{
    public function home(string $slug): RedirectResponse
    {
        $store = $this->store($slug);
        $page = $store->pages()->active()->ordered()->firstOrFail();

        return redirect()->route('vitrine.public.page', [
            'slug' => $store->slug,
            'page' => $page->key,
        ]);
    }

    public function page(string $slug, string $pageKey): View
    {
        $store = $this->store($slug);
        $page = $store->pages()->active()->where('key', $pageKey)->firstOrFail();

        return $this->renderStorefront($store, $page);
    }

    public function pageWithId(string $slug, string $pageKey, int $id): View
    {
        $store = $this->store($slug);
        $page = $store->pages()->active()->where('key', $pageKey)->firstOrFail();
        abort_unless($page->type === 'products', 404);

        $product = $store->products()
            ->whereKey($id)
            ->where('is_public', true)
            ->with([
                'category:id,name,slug',
                'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('id'),
                'reviews' => fn ($query) => $query->approved()->latest(),
            ])
            ->withAvg(['reviews as approved_rating' => fn ($query) => $query->approved()], 'rating')
            ->withCount(['reviews as approved_reviews_count' => fn ($query) => $query->approved()])
            ->firstOrFail();

        return $this->renderStorefront($store, $page, $product);
    }

    public function storeReview(Request $request, string $slug, Product $product): RedirectResponse
    {
        $store = $this->store($slug);
        abort_unless($product->user_id === $store->id && $product->is_public, 404);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:2000'],
        ], [
            'customer_name.required' => 'Informe seu nome.',
            'rating.required' => 'Escolha uma nota de 1 a 5.',
            'rating.between' => 'A nota deve estar entre 1 e 5.',
            'comment.required' => 'Escreva um comentário sobre o produto.',
        ]);

        Review::query()->create([
            ...$data,
            'user_id' => $store->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Avaliação enviada. Ela será publicada após a aprovação da loja.');
    }

    private function renderStorefront(User $store, Page $page, ?Product $product = null): View
    {
        $store->loadMissing('settings');
        $pages = $store->pages()->active()->ordered()->get();
        $products = collect();
        $categories = collect();
        $reviews = collect();
        $banners = collect();

        if ($page->type === 'products' && ! $product) {
            $categories = $store->categories()->where('is_active', true)->orderBy('order')->get();
            $products = $store->products()
                ->where('is_public', true)
                ->with(['category:id,name,slug', 'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('id')])
                ->withAvg(['reviews as approved_rating' => fn ($query) => $query->approved()], 'rating')
                ->withCount(['reviews as approved_reviews_count' => fn ($query) => $query->approved()])
                ->latest()
                ->get();
            $banners = $store->banners()->where('is_active', true)->orderBy('order')->get();
        }

        if ($page->type === 'reviews') {
            $reviews = $store->reviews()->approved()->with('product:id,name')->latest()->get();
        }

        return view('storefront.show', compact(
            'store', 'page', 'pages', 'products', 'categories', 'reviews', 'banners', 'product'
        ));
    }

    private function store(string $slug): User
    {
        return User::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
    }
}
