<?php
namespace App\Http\Controllers\Vitrine;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VitrineController extends Controller
{
    public function home(string $slug)
    {
        return $this->renderPage($slug);
    }

    public function page(string $slug, string $pageKey)
    {
        return $this->renderPage($slug, $pageKey);
    }

    public function pageWithId(string $slug, string $pageKey, int $id)
    {
        return $this->renderPage($slug, $pageKey, $id);
    }

    private function renderPage(string $slug, ?string $pageKey = null, int $id = null)
    {
        // ===== BUSCA DO USUÁRIO =====
        $user = User::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'settings',
                'pages' => function ($q) {
                    $q
                        ->where('is_active', true)
                        ->orderBy('order')
                        ->orderBy('id');
                },
                'products.images',
                'categories',
                'products.reviews' => fn($q) => $q->where('status', 'approved'),
                'reviews' => fn($q) => $q->where('status', 'approved'),
            ])
            ->firstOrFail();

        // ===== PROCESSAR PÁGINAS =====
        $pages = $user->pages->map(function ($page) {
            $content = $page->content;

            if (in_array($page->type, ['links', 'gallery'])) {
                $content = json_decode($content, true) ?? [];
            }

            return [
                'id' => $page->id,
                'key' => $page->key,
                'title' => $page->title,
                'icon' => $page->icon,
                'type' => $page->type,
                'is_active' => $page->is_active,
                'order' => $page->order,
                'cover_image' => $page->cover_image,
                'seo_title' => $page->seo_title,
                'seo_description' => $page->seo_description,
                'content' => $content,
            ];
        });

        // ===== DEFINIR A PÁGINA ATUAL A PARTIR DA ROTA REAL =====
        if ($pageKey) {
            $currentPage = $pages->firstWhere('key', $pageKey)
                ?? abort(404);
        } else {
            // Página inicial = primeira página ativa
            $currentPage = $pages->first();
        }

        // ===== DADOS RELACIONADOS À PÁGINA =====
        $products = $user->products()
    ->with([
        'images',
        'reviews' => fn($q) => $q->where('status', 'approved')
    ])
    ->where('is_public', true)
    ->get()
    ->map(function($product){
        $product->rating = $product->reviews->avg('rating') ?? null;
        return $product;
    });

        $categories = $user->categories;
        $reviews = $user->reviews;

        if ($id) {
            return Inertia::render('Vitrine/ShowId', [
                'user' => $user,
                'settings' => $user->settings,
                'pages' => $pages,
                'page' => $currentPage,
                'products' => $products,
                'categories' => $categories,
                'itemId' => $id,  // opcional
            ]);
        } else {
            return Inertia::render('Vitrine/Show', [
                'user' => $user,
                'settings' => $user->settings,
                'pages' => $pages,
                'page' => $currentPage,
                'products' => $products,
                'categories' => $categories,
                'reviews' => $reviews,
                'itemId' => $id,  // opcional
            ]);
        }
    }
}
