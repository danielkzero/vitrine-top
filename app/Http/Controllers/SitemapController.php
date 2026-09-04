<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $stores = User::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->with([
                'pages' => fn ($query) => $query->active()->ordered(),
                'products' => fn ($query) => $query->where('is_public', true)->latest('updated_at'),
            ])
            ->get();

        return response()
            ->view('storefront.sitemap', compact('stores'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
