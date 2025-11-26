<?php

namespace App\Http\Controllers\Vitrine;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VitrineController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Buscar usuário ativo pela vitrine
        $user = User::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'settings',
                'pages' => function ($q) {
                    $q->where('is_active', true)
                      ->orderBy('order')
                      ->orderBy('id');
                },
                'products.images',
                'categories',
                'reviews' => function ($q) {
                    $q->where('status', 'approved');
                }
            ])
            ->firstOrFail();

        // Montar páginas com conteúdo decodificado
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

        // Página atual baseada no ?p=key
        $requestedKey = $request->query('p');

        $currentPage = $pages->firstWhere('key', $requestedKey)
            ?? $pages->first(); // fallback

        // Produtos públicos
        $products = $user->products
            ->filter(fn ($p) => $p->is_public)
            ->values();

        // Categorias
        $categories = $user->categories;

        // Reviews aprovadas
        $reviews = $user->reviews;

        return Inertia::render('Vitrine/Show', [
            'user' => $user,
            'settings' => $user->settings,
            'pages' => $pages,
            'page' => $currentPage,  // EXATAMENTE AQUI COMO VOCÊ QUERIA
            'products' => $products,
            'categories' => $categories,
            'reviews' => $reviews,
        ]);
    }
}
