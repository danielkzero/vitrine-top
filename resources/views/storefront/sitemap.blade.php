<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($stores as $store)
    @foreach($store->pages as $page)
    <url>
        <loc>{{ route('vitrine.public.page', [$store->slug, $page->key]) }}</loc>
        <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
        <changefreq>{{ $page->type === 'products' ? 'daily' : 'monthly' }}</changefreq>
        <priority>{{ $page->type === 'products' ? '0.9' : '0.6' }}</priority>
    </url>
        @if($page->type === 'products')
            @foreach($store->products as $product)
    <url>
        <loc>{{ route('vitrine.public.page.id', [$store->slug, $page->key, $product->id]) }}</loc>
        <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
            @endforeach
        @endif
    @endforeach
@endforeach
</urlset>
