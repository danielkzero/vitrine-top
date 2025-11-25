<?php

namespace App\Http\Controllers\Vitrine;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class VitrineController extends Controller
{
    public function show($slug)
    {
        // 1) Buscar usuário
        $user = User::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'settings',
                'pages' => function ($q) {
                    $q->where('is_active', true)
                      ->orderBy('order')->orderBy('id');
                },
                'products.images',
                'categories',
                'reviews' => function ($q) {
                    $q->where('status', 'approved');
                },
            ])
            ->firstOrFail();

        // 2) Montar páginas já decodificadas
        $pages = $user->pages->map(function ($page) {
            $content = $page->content;

            if (in_array($page->type, ['links', 'gallery'])) {
                $content = json_decode($content, true) ?? [];
            }

            return [
                'id' => $page->id,
                'key' => $page->key,
                'title' => $page->title,
                'type' => $page->type,
                'icon' => $page->icon,
                'seo_title' => $page->seo_title,
                'seo_description' => $page->seo_description,
                'cover_image' => $page->cover_image,
                'content' => $content
            ];
        });

        return Inertia::render('Vitrine/Index', [
            'user' => $user,
            'pages' => $pages,
            'products' => $user->products,
            'categories' => $user->categories,
            'reviews' => $user->reviews,
            'settings' => $user->settings,
        ]);
    }
}
