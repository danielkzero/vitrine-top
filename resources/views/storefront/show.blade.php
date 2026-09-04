@php
    $assetUrl = static function (?string $path): ?string {
        if (! $path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:') || str_starts_with($path, '/')) return $path;
        return asset('storage/'.ltrim($path, '/'));
    };
    $firstProductImage = $product?->images?->first();
    $seoTitle = $product?->seo_title ?: ($product?->name ? $product->name.' | '.$store->business_name : ($page->seo_title ?: $page->title.' | '.$store->business_name));
    $seoDescription = str($product?->seo_description ?: $product?->description ?: $page->seo_description ?: $store->description)->stripTags()->squish()->limit(160);
    $socialImage = $assetUrl($firstProductImage?->image_path ?: $product?->cover_image ?: $store->background_path ?: $store->logo_path);
    $themeColor = $store->theme_color ?: $store->settings?->theme_color ?: '#059669';
    $phone = preg_replace('/\D+/', '', $store->whatsapp ?: $store->phone_primary ?: '');
    $canonical = url()->current();
    $structuredData = $product ? [
        '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name,
        'description' => str($product->description)->stripTags()->squish()->toString(),
        'image' => $product->images->map(fn ($image) => $assetUrl($image->image_path))->filter()->values()->all(),
        'sku' => $product->code, 'brand' => ['@type' => 'Brand', 'name' => $store->business_name],
        'offers' => ['@type' => 'Offer', 'url' => $canonical, 'priceCurrency' => 'BRL', 'price' => (float) ($product->discount_price > 0 ? $product->discount_price : $product->price), 'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'],
        ...($product->approved_reviews_count ? ['aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => round((float) $product->approved_rating, 1), 'reviewCount' => $product->approved_reviews_count]] : []),
    ] : ['@context' => 'https://schema.org', '@type' => 'Store', 'name' => $store->business_name, 'description' => str($store->description)->stripTags()->squish()->toString(), 'url' => route('vitrine.public.home', $store->slug), 'logo' => $assetUrl($store->logo_path), 'telephone' => $store->whatsapp ?: $store->phone_primary];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}"><meta name="robots" content="index, follow"><meta name="theme-color" content="{{ $themeColor }}"><link rel="canonical" href="{{ $canonical }}">
    @if($store->logo_path)<link rel="icon" href="{{ $assetUrl($store->logo_path) }}">@endif
    <meta property="og:type" content="{{ $product ? 'product' : 'website' }}"><meta property="og:locale" content="pt_BR"><meta property="og:site_name" content="{{ $store->business_name }}"><meta property="og:title" content="{{ $seoTitle }}"><meta property="og:description" content="{{ $seoDescription }}"><meta property="og:url" content="{{ $canonical }}">@if($socialImage)<meta property="og:image" content="{{ url($socialImage) }}">@endif<meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @vite('resources/css/app.css')
    <style>[hidden]{display:none!important}.store-content{line-height:1.65}.store-content h2{font-size:1.5rem;font-weight:700;margin:1.25rem 0 .5rem}.store-content h3{font-size:1.15rem;font-weight:700;margin:1rem 0 .4rem}.store-content p{margin:.6rem 0}</style>
</head>
<body class="min-h-screen bg-[#f5f5f7] font-sans text-slate-900">
<div class="flex min-h-screen flex-col">
    @unless($product)
        <header class="container-custom relative mx-auto flex h-[10rem] w-full items-center justify-center overflow-hidden rounded-b-3xl bg-cover bg-center bg-no-repeat shadow-xl md:mt-0 md:h-[20rem] md:rounded-none md:shadow-none" style="background-image:url('{{ $assetUrl($store->background_path) }}')">
            <div class="absolute inset-0 rounded-b-3xl bg-gradient-to-t from-black/65 to-black/35 backdrop-blur-[1px] md:rounded-none"></div>
            <div class="relative z-10 px-4 text-center text-white md:px-8">
                <h1 class="flex items-center justify-center text-4xl font-extrabold drop-shadow-lg md:text-5xl">@if($store->logo_path)<img src="{{ $assetUrl($store->logo_path) }}" alt="Logo de {{ $store->business_name }}" class="mr-3 w-14 rounded-2xl bg-white/90 shadow md:w-16">@endif {{ $store->business_name }}</h1>
                @if($store->subtitle)<p class="mt-3 text-lg opacity-90 md:text-xl">{{ $store->subtitle }}</p>@endif
            </div>
            <div class="absolute right-4 top-4 z-20 flex gap-2 md:hidden"><button type="button" data-open-account class="rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow"><span data-account-button-label>Entrar</span></button><button type="button" data-open-cart class="rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow">Carrinho <span data-cart-count>0</span></button></div>
        </header>
    @endunless

    <nav class="sticky top-0 z-40 hidden w-full border-b border-slate-200/80 bg-white/85 backdrop-blur-xl md:block" aria-label="Navegação da loja">
        <div class="container-custom flex items-center justify-between gap-4 px-6 py-2.5 lg:px-10">
            <div class="flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-1 shadow-sm">
                @foreach($pages as $navPage)<a href="{{ route('vitrine.public.page', [$store->slug, $navPage->key]) }}" class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-medium transition {{ $navPage->id === $page->id ? 'text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}" @if($navPage->id === $page->id) style="background-color:{{ $themeColor }}" @endif>{{ $navPage->title }}</a>@endforeach
            </div>
            <div class="flex items-center gap-1"><button type="button" data-open-account class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"><span data-account-button-label>Entrar</span></button><button type="button" data-open-cart class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">Carrinho <span data-cart-count>0</span></button>@if($phone)<a href="https://wa.me/{{ $phone }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50">WhatsApp</a>@endif</div>
        </div>
    </nav>

    <main class="container-custom mx-auto flex-grow {{ $product ? 'px-0' : 'px-3 md:px-10' }} md:pt-6">
        @if(session('success'))<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900" role="alert"><strong>Revise os dados:</strong><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        @if($product)
            @include('storefront.product')
        @elseif($page->type === 'products')
            <header class="pb-4 lg:hidden">
                @if($banners->isNotEmpty())@include('storefront.banner-carousel', ['class' => 'mt-4 h-44 w-full select-none overflow-hidden rounded-2xl bg-white shadow-[0_8px_25px_rgba(0,0,0,0.12)]'])@endif
                <div class="mt-4 flex items-center gap-3">
                    <button type="button" data-open-cart class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-100 bg-white text-slate-700 shadow-sm">@include('storefront.icon', ['name' => 'ShoppingCart', 'attributes' => 'class="h-5 w-5"'])<span data-cart-count class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] text-white">0</span></button>
                    <div class="relative flex-1"><span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate-400">@include('storefront.icon', ['name' => 'Search', 'attributes' => 'class="h-4 w-4"'])</span><input data-search type="search" placeholder="Pesquisar produtos..." class="h-10 w-full rounded-xl border border-slate-100 bg-white py-2 pr-3 pl-10 text-sm shadow-sm"></div>
                    <button type="button" data-toggle-view class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-100 bg-white text-slate-700 shadow-sm" aria-label="Alternar visualização">@include('storefront.icon', ['name' => 'LayoutGrid', 'attributes' => 'class="h-5 w-5"'])</button>
                </div>
                <h2 class="my-6 flex items-center text-xl font-bold md:text-2xl">Categorias</h2>
                <div class="mt-3 overflow-x-auto pb-2"><div class="flex gap-2"><button data-category="all" type="button" class="category-filter whitespace-nowrap rounded-xl px-3 py-1.5 text-sm text-white" style="background-color:{{ $themeColor }}">Todas</button>@foreach($categories as $category)<button data-category="{{ $category->id }}" type="button" class="category-filter whitespace-nowrap rounded-xl border bg-white px-3 py-1.5 text-sm">{{ $category->name }}</button>@endforeach</div></div>
            </header>

            <div class="pb-2 lg:grid lg:grid-cols-[280px_minmax(0,1fr)] lg:gap-8">
                <aside class="hidden lg:block"><div class="sticky top-20 space-y-5">
                    <button type="button" data-open-cart class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm transition hover:shadow-md"><span>🛒 Carrinho</span><span data-cart-count class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-900 px-2 text-xs text-white">0</span></button>
                    <div class="space-y-3"><input data-search type="search" placeholder="Pesquisar produto" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
                    <div class="space-y-2"><h3 class="text-sm font-semibold uppercase tracking-wide text-slate-800">Categorias</h3><div class="space-y-1 border-l border-slate-200 pl-3"><button data-category="all" type="button" class="category-filter w-full px-2 py-2 text-left text-sm font-semibold text-slate-900">Todas</button>@foreach($categories as $category)<button data-category="{{ $category->id }}" type="button" class="category-filter w-full px-2 py-2 text-left text-sm text-slate-600 transition hover:text-slate-900">{{ $category->name }}</button>@endforeach</div></div>
                </div></aside>
                <section>
                    <h2 class="hidden items-center py-4 text-xl font-bold md:py-2 md:text-2xl lg:flex">{{ $page->title }}</h2>
                    @if($banners->isNotEmpty())@include('storefront.banner-carousel', ['class' => 'hidden h-[19rem] w-full select-none overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-[0_18px_42px_rgba(15,23,42,0.12)] lg:block'])@endif
                    <div class="mt-3 mb-4 hidden items-center justify-end text-sm text-slate-500 lg:flex"><span data-results-count>{{ $products->count() }} itens</span></div>
                    <div data-product-grid class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                        @foreach($products as $item)
                            @php $image = $item->images->first(); $salePrice = $item->discount_price > 0 ? $item->discount_price : $item->price; @endphp
                            <article data-product-card data-name="{{ str($item->name.' '.$item->description)->lower() }}" data-category="{{ $item->category_id }}" class="relative flex h-full cursor-pointer flex-col overflow-hidden rounded-xl border border-slate-200/80 bg-white p-3 shadow-[0_14px_28px_rgba(15,23,42,0.08)] transition hover:shadow-[0_20px_36px_rgba(15,23,42,0.12)]">
                                @if($item->featured)<span class="absolute top-2 left-2 z-20 flex items-center gap-1 rounded-md bg-amber-100/80 px-2 py-1 text-xs" style="color:{{ $themeColor }}">☆ Destaque</span>@endif
                                <span class="absolute top-2 right-2 z-20 text-xl text-slate-400" aria-hidden="true">♡</span>
                                <a href="{{ route('vitrine.public.page.id', [$store->slug, $page->key, $item->id]) }}" class="mb-3 flex h-28 items-center justify-center rounded-lg md:h-40 md:bg-slate-50">@if($image)<img src="{{ $assetUrl($image->image_path) }}" alt="{{ $item->name }}" class="max-h-full object-contain md:scale-[1.02]" loading="lazy">@endif</a>
                                <h3 class="text-sm font-semibold text-slate-800"><a href="{{ route('vitrine.public.page.id', [$store->slug, $page->key, $item->id]) }}">{{ $item->name }}</a></h3>
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $item->description }}</p>
                                <div class="mt-2">@if($item->discount_price > 0)<div class="text-xs text-slate-400 line-through">R$ {{ number_format((float)$item->price,2,',','.') }}</div>@endif<div class="font-bold" style="color:{{ $themeColor }}">R$ {{ number_format((float)$salePrice,2,',','.') }}</div>@if($item->stock)<div class="text-xs text-slate-400">Estoque: {{ $item->stock }}</div>@endif</div>
                                <div class="mt-auto pt-3">
                                    @if(($item->conversion_type ?: 'cart') === 'external' && $item->external_url)<a class="block w-full rounded-lg py-2.5 text-center text-sm font-medium text-white" style="background-color:{{ $themeColor }}" href="{{ $item->external_url }}" target="_blank" rel="nofollow sponsored noopener">{{ $item->cta_label ?: 'Ver oferta' }}</a>
                                    @elseif(($item->conversion_type ?: 'cart') === 'whatsapp')<a class="block w-full rounded-lg py-2.5 text-center text-sm font-medium text-white" style="background-color:{{ $themeColor }}" href="https://wa.me/{{ $phone }}?text={{ urlencode('Olá, tenho interesse em '.$item->name) }}" target="_blank" rel="noopener">{{ $item->cta_label ?: 'Falar no WhatsApp' }}</a>
                                    @else<button class="w-full rounded-lg py-2.5 text-sm font-medium text-white" style="background-color:{{ $themeColor }}" type="button" data-add-cart data-product-id="{{ $item->id }}" data-product-name="{{ $item->name }}" data-product-price="{{ (float)$salePrice }}" {{ $item->stock <= 0 ? 'disabled' : '' }}>{{ $item->cta_label ?: 'Adicionar ao carrinho' }}</button>@endif
                                </div>
                            </article>
                        @endforeach
                    </div><p data-no-results hidden class="py-12 text-center text-slate-400">Nenhum produto disponível.</p>
                </section>
            </div>
        @elseif($page->type === 'reviews')
            <h1 class="flex items-center py-6 text-xl font-bold md:text-2xl">{{ $page->title }}</h1><div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">@forelse($reviews as $review)<article class="rounded-xl border bg-white p-4"><div class="flex items-center justify-between"><strong>{{ $review->customer_name }}</strong><small class="text-slate-400">{{ $review->created_at->format('d/m/Y') }}</small></div><div class="mt-2 flex gap-1 text-yellow-400" aria-label="Nota {{ $review->rating }} de 5">{{ str_repeat('★', $review->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5-$review->rating) }}</span></div><p class="mt-2 text-sm text-slate-600">{{ $review->comment }}</p></article>@empty<p class="text-slate-500">A loja ainda não possui avaliações publicadas.</p>@endforelse</div>
        @elseif($page->type === 'links')
            @php $links=json_decode($page->content ?: '[]',true) ?: []; @endphp<h1 class="flex items-center py-6 text-xl font-bold md:text-2xl">{{ $page->title }}</h1><div class="grid grid-cols-1 gap-4 sm:grid-cols-2">@foreach($links as $link)<a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-xl border bg-white p-4 transition hover:shadow"><div><strong>{{ $link['text'] ?? 'Abrir link' }}</strong><div class="text-xs text-slate-400">{{ $link['url'] ?? '' }}</div></div></a>@endforeach</div>
        @else
            <article class="store-content py-6"><h1 class="mb-5 text-2xl font-bold">{{ $page->title }}</h1>{!! $page->content !!}</article>
        @endif
    </main>

    <footer class="mt-6 pb-24 md:pb-8"><div class="container-custom mx-auto px-4 py-6 text-sm text-slate-600 md:px-10"><h2 class="mb-3 flex items-center text-xl font-extrabold">Sobre</h2><p class="leading-relaxed">{{ $store->description }}</p></div><div class="container-custom mx-auto px-4 py-6 text-center text-sm text-slate-500">&copy; {{ now()->year }} {{ $store->business_name }}<div class="mt-2 text-xs">Desenvolvido por <a href="{{ url('/') }}" class="font-extrabold">vitrine.top</a></div></div></footer>

    <nav class="fixed right-3 bottom-4 left-3 z-40 md:hidden" aria-label="Navegação principal"><div class="mx-auto flex max-w-xl items-center justify-around rounded-2xl border border-slate-100 bg-white px-3 py-3 shadow-[0_8px_24px_rgba(15,23,42,0.16)]">@foreach($pages->take(5) as $navPage)<a href="{{ route('vitrine.public.page', [$store->slug,$navPage->key]) }}" class="relative flex min-w-0 flex-col items-center gap-1 px-2 py-1 text-[10px] font-medium" style="color:{{ $navPage->id === $page->id ? $themeColor : '#64748b' }}">@include('storefront.icon', ['name' => $navPage->icon, 'attributes' => 'class="h-6 w-6"'])<span class="max-w-[64px] truncate">{{ $navPage->title }}</span>@if($navPage->id === $page->id)<span class="absolute -bottom-1.5 h-1.5 w-1.5 rounded-full" style="background-color:{{ $themeColor }}"></span>@endif</a>@endforeach</div></nav>

    @include('storefront.customer-panel')
    <div data-cart-drawer hidden class="fixed inset-0 z-50 bg-slate-950/50"><aside class="absolute top-0 right-0 h-full w-full max-w-lg overflow-y-auto bg-white p-6 shadow-2xl"><div class="flex items-center justify-between"><h2 class="text-xl font-bold">Seu carrinho</h2><button type="button" data-close-cart class="h-9 w-9 rounded-full bg-slate-100 text-xl">×</button></div><div data-cart-message hidden class="mt-3 rounded-lg px-3 py-2 text-sm"></div><div data-cart-items></div><div data-cart-summary></div><div data-checkout-guest class="mt-4 rounded-xl bg-slate-50 p-4 text-sm"><p>Entre ou crie sua conta para informar o endereço e finalizar o pedido.</p><button type="button" data-open-account class="mt-3 w-full rounded-lg py-2 font-semibold text-white" style="background-color:{{ $themeColor }}">Entrar para finalizar</button></div><form data-checkout-form hidden class="mt-4 space-y-3 rounded-xl bg-slate-50 p-4"><h3 class="font-bold">Finalizar pedido</h3><label class="block text-sm">Endereço<select name="address_id" data-checkout-address required class="mt-1 w-full rounded-lg border bg-white p-2"></select></label><label class="block text-sm">Forma de pagamento<select name="payment_method" required class="mt-1 w-full rounded-lg border bg-white p-2"><option value="pix">PIX</option><option value="cash">Dinheiro</option><option value="credit_card">Cartão</option><option value="manual">Combinar com a loja</option></select></label><label class="block text-sm">Entrega<select name="shipping_method" class="mt-1 w-full rounded-lg border bg-white p-2"><option value="delivery">Entrega</option><option value="retirada">Retirada</option></select></label><textarea name="notes" rows="2" placeholder="Observações do pedido" class="w-full rounded-lg border bg-white p-2"></textarea><button class="w-full rounded-lg py-2.5 font-semibold text-white" style="background-color:{{ $themeColor }}">Confirmar pedido</button></form>@if($phone)<a data-whatsapp-order hidden href="#" target="_blank" rel="noopener" class="mt-3 block w-full rounded-lg border py-2.5 text-center font-medium" style="border-color:{{ $themeColor }};color:{{ $themeColor }}">Enviar pelo WhatsApp</a>@endif</aside></div>
</div>
<script>
(()=>{const key=@json('vitrine-cart-'.$store->slug),phone=@json($phone);let cart=JSON.parse(localStorage.getItem(key)||'[]');const money=v=>Number(v).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}),escape=v=>{const d=document.createElement('div');d.textContent=v;return d.innerHTML};function render(){const count=cart.reduce((s,i)=>s+i.quantity,0);document.querySelectorAll('[data-cart-count]').forEach(e=>e.textContent=count);const list=document.querySelector('[data-cart-items]'),summary=document.querySelector('[data-cart-summary]');if(!list||!summary)return;list.innerHTML=cart.length?cart.map(i=>`<div class="flex items-center justify-between gap-3 border-b border-slate-200 py-4"><div class="min-w-0"><strong class="block truncate">${escape(i.name)}</strong><small>${money(i.price)} cada</small></div><div class="flex shrink-0 items-center gap-2"><button type="button" aria-label="Diminuir quantidade" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-xl font-semibold text-slate-700" data-cart-decrease="${i.id}">−</button><strong class="w-6 text-center">${i.quantity}</strong><button type="button" aria-label="Aumentar quantidade" class="flex h-9 w-9 items-center justify-center rounded-full text-xl font-semibold text-white" style="background-color:{{ $themeColor }}" data-cart-increase="${i.id}">+</button></div></div>`).join(''):'<p class="py-8 text-center text-slate-500">Seu carrinho está vazio.</p>';const total=cart.reduce((s,i)=>s+i.price*i.quantity,0);summary.innerHTML=cart.length?`<p class="mt-4 font-bold">Total: ${money(total)}</p>`:'';const w=document.querySelector('[data-whatsapp-order]');if(w){w.hidden=!cart.length;w.href=`https://wa.me/${phone}?text=${encodeURIComponent(['Olá, quero fazer um pedido em {{ addslashes($store->business_name) }}:','',...cart.map(i=>`- ${i.quantity}x ${i.name}`),'',`Total: ${money(total)}`].join('\n'))}`}}const save=()=>{localStorage.setItem(key,JSON.stringify(cart));render()};document.addEventListener('click',e=>{const add=e.target.closest('[data-add-cart]');if(add){const i={id:Number(add.dataset.productId),name:add.dataset.productName,price:Number(add.dataset.productPrice)},old=cart.find(x=>x.id===i.id);old?old.quantity++:cart.push({...i,quantity:1});save();document.querySelector('[data-cart-drawer]').hidden=false}if(e.target.closest('[data-open-cart]'))document.querySelector('[data-cart-drawer]').hidden=false;if(e.target.closest('[data-close-cart]')||e.target.matches('[data-cart-drawer]'))document.querySelector('[data-cart-drawer]').hidden=true;const decrease=e.target.closest('[data-cart-decrease]'),increase=e.target.closest('[data-cart-increase]');if(decrease||increase){const id=Number((decrease||increase).dataset[decrease?'cartDecrease':'cartIncrease']),item=cart.find(i=>i.id===id);if(item){item.quantity+=increase?1:-1;if(item.quantity<=0)cart=cart.filter(i=>i.id!==id);save()}}const thumb=e.target.closest('[data-product-thumb]');if(thumb)document.querySelector('[data-product-main]').src=thumb.src});const searches=document.querySelectorAll('[data-search]');let category='all';function filter(){const q=[...searches].map(x=>x.value.toLowerCase()).find(Boolean)||'';let visible=0;document.querySelectorAll('[data-product-card]').forEach(card=>{const ok=card.dataset.name.includes(q)&&(category==='all'||card.dataset.category===category);card.hidden=!ok;if(ok)visible++});const count=document.querySelector('[data-results-count]');if(count)count.textContent=`${visible} itens`;const empty=document.querySelector('[data-no-results]');if(empty)empty.hidden=visible>0}searches.forEach(x=>x.addEventListener('input',e=>{searches.forEach(other=>{if(other!==e.target)other.value=e.target.value});filter()}));document.querySelectorAll('[data-category]').forEach(b=>b.addEventListener('click',()=>{category=b.dataset.category;document.querySelectorAll('[data-category]').forEach(x=>{x.classList.toggle('font-semibold',x.dataset.category===category);if(x.closest('header')){x.classList.toggle('text-white',x.dataset.category===category);x.classList.toggle('bg-white',x.dataset.category!==category);x.style.backgroundColor=x.dataset.category===category?'{{ $themeColor }}':''}});filter()}));const ratingInputs=document.querySelectorAll('[data-rating-input]'),ratingLabel=document.querySelector('[data-rating-label]');function paintRating(value){document.querySelectorAll('[data-rating-star]').forEach(star=>{const active=Number(star.dataset.value)<=value;star.classList.toggle('text-amber-400',active);star.classList.toggle('text-slate-300',!active)});if(ratingLabel)ratingLabel.textContent=value?`${value} de 5`:'Selecione uma nota'}ratingInputs.forEach(input=>input.addEventListener('change',()=>paintRating(Number(input.value))));const checked=document.querySelector('[data-rating-input]:checked');paintRating(checked?Number(checked.value):0);render()})();
</script>
<script>
document.querySelectorAll('[data-banner-carousel]').forEach(carousel => {
    const track = carousel.querySelector('[data-banner-track]');
    const slides = carousel.querySelectorAll('[data-banner-slide]');
    const dots = carousel.querySelectorAll('[data-banner-dot]');
    if (!track || slides.length < 2) return;

    let current = 0;
    const show = index => {
        current = index;
        track.style.transform = `translateX(-${current * 100}%)`;
        dots.forEach((dot, dotIndex) => {
            dot.classList.toggle('w-4', dotIndex === current);
            dot.classList.toggle('w-1.5', dotIndex !== current);
            dot.classList.toggle('bg-white', dotIndex === current);
        });
    };
    dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
    setInterval(() => show((current + 1) % slides.length), 4000);
});
</script>
<script>
(() => {
    const reviewButton = document.querySelector('[data-toggle-review]');
    const reviewForm = document.querySelector('[data-review-form]');
    const reviewLabel = document.querySelector('[data-review-toggle-label]');
    reviewButton?.addEventListener('click', () => {
        reviewForm.hidden = !reviewForm.hidden;
        reviewLabel.textContent = reviewForm.hidden ? 'Avaliar' : 'Fechar';
        if (!reviewForm.hidden) reviewForm.querySelector('input')?.focus();
    });
    @if($errors->any())
    if (reviewForm) { reviewForm.hidden = false; if (reviewLabel) reviewLabel.textContent = 'Fechar'; }
    @endif

    const grid = document.querySelector('[data-product-grid]');
    document.querySelector('[data-toggle-view]')?.addEventListener('click', () => {
        if (!grid) return;
        const list = grid.dataset.view === 'list';
        grid.dataset.view = list ? 'grid' : 'list';
        grid.classList.toggle('grid-cols-1', !list);
        grid.classList.toggle('grid-cols-2', list);
    });

    const favoriteButton = document.querySelector('[data-toggle-favorite]');
    const favoriteKey = @json('vitrine-favorites-'.$store->slug);
    const productId = @json($product?->id);
    if (favoriteButton && productId) {
        let favorites = JSON.parse(localStorage.getItem(favoriteKey) || '[]');
        const paint = () => favoriteButton.classList.toggle('text-rose-500', favorites.includes(productId));
        paint();
        favoriteButton.addEventListener('click', () => {
            favorites = favorites.includes(productId) ? favorites.filter(id => id !== productId) : [...favorites, productId];
            localStorage.setItem(favoriteKey, JSON.stringify(favorites));
            paint();
        });
    }
})();
</script>
<script>
(() => {
    const slug = @json($store->slug);
    const tokenKey = `store_customer_token:${slug}`;
    const guestKey = @json('vitrine-cart-'.$store->slug);
    let token = localStorage.getItem(tokenKey);
    let customer = null, addresses = [], orders = [], serverCart = null, serverTotals = null;
    const accountDrawer = document.querySelector('[data-account-drawer]');
    const cartDrawer = document.querySelector('[data-cart-drawer]');
    const message = document.querySelector('[data-account-message]');
    const cartMessage = document.querySelector('[data-cart-message]');
    const esc = value => { const div=document.createElement('div'); div.textContent=value ?? ''; return div.innerHTML; };
    const money = value => Number(value || 0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'});
    const statusNames = {pending:'Pendente',confirmed:'Confirmado',preparing:'Em preparação',shipped:'Saiu para entrega',delivered:'Entregue',canceled:'Cancelado'};

    async function api(path, options={}) {
        const response = await fetch(path, { ...options, headers:{'Accept':'application/json','Content-Type':'application/json',...(token?{'Authorization':`Bearer ${token}`}:{}) ,...(options.headers||{})} });
        const data = await response.json().catch(()=>({}));
        if (!response.ok) { const errors=data.errors?Object.values(data.errors).flat().join(' '):null; throw new Error(errors || data.message || 'Não foi possível concluir a operação.'); }
        return data;
    }
    function notify(element, text, error=false) { if(!element)return; element.hidden=false; element.textContent=text; element.className=`mt-3 rounded-lg px-3 py-2 text-sm ${error?'bg-red-50 text-red-800':'bg-emerald-50 text-emerald-800'}`; }
    function paintAccount() {
        document.querySelector('[data-account-guest]').hidden=!!customer;
        document.querySelector('[data-account-auth]').hidden=!customer;
        document.querySelectorAll('[data-account-button-label]').forEach(el=>el.textContent=customer?'Minha conta':'Entrar');
        document.querySelector('[data-account-subtitle]').textContent=customer?`Olá, ${customer.name}`:'Entre para finalizar pedidos e acompanhar entregas';
        document.querySelector('[data-checkout-guest]').hidden=!!customer;
        document.querySelector('[data-checkout-form]').hidden=!customer;
        if(!customer)return;
        document.querySelector('[data-customer-name]').textContent=customer.name;
        document.querySelector('[data-customer-contact]').textContent=customer.email || customer.whatsapp || '';
        renderAddresses(); renderOrders(); renderServerCart();
    }
    function renderAddresses() {
        const list=document.querySelector('[data-address-list]'), select=document.querySelector('[data-checkout-address]');
        if(list) list.innerHTML=addresses.length?addresses.map(a=>`<article class="rounded-xl border p-3"><div class="flex justify-between"><strong>${esc(a.label||'Endereço')}</strong>${a.is_default?'<small class="text-emerald-600">Principal</small>':''}</div><p class="mt-1 text-sm text-slate-600">${esc(a.street)}, ${esc(a.number)} · ${esc(a.neighborhood)}<br>${esc(a.city)}/${esc(a.state)} · ${esc(a.zip)}</p></article>`).join(''):'<p class="text-sm text-slate-500">Nenhum endereço cadastrado.</p>';
        if(select) select.innerHTML=addresses.map(a=>`<option value="${a.id}">${esc(a.label||'Endereço')} — ${esc(a.street)}, ${esc(a.number)}</option>`).join('');
    }
    function renderOrders() {
        const panel=document.querySelector('[data-orders-panel]'); if(!panel)return;
        panel.innerHTML=orders.length?orders.map(o=>`<article class="rounded-xl border p-4"><div class="flex justify-between gap-3"><strong>Pedido ${esc(o.order_number||'#'+o.id)}</strong><span class="rounded-full bg-slate-100 px-2 py-1 text-xs">${esc(statusNames[o.status]||o.status)}</span></div><p class="mt-2 text-sm text-slate-600">${(o.items||[]).length} itens · ${money(o.total)}</p><small class="text-slate-400">${new Date(o.created_at).toLocaleDateString('pt-BR')}</small></article>`).join(''):'<p class="py-5 text-center text-sm text-slate-500">Você ainda não fez pedidos nesta loja.</p>';
    }
    function renderServerCart() {
        if(!customer||!serverCart)return; const items=serverCart.items||[], list=document.querySelector('[data-cart-items]'), summary=document.querySelector('[data-cart-summary]');
        list.innerHTML=items.length?items.map(i=>`<div class="flex items-center justify-between gap-3 border-b border-slate-200 py-4"><div class="min-w-0"><strong class="block truncate">${esc(i.product?.name||'Produto')}</strong><small>${money(i.unit_price)} cada</small></div><div class="flex shrink-0 items-center gap-2"><button type="button" aria-label="Diminuir quantidade" class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-xl font-semibold text-slate-700" data-server-quantity="${i.id}" data-quantity="${i.quantity-1}">−</button><strong class="w-6 text-center">${i.quantity}</strong><button type="button" aria-label="Aumentar quantidade" class="flex h-9 w-9 items-center justify-center rounded-full text-xl font-semibold text-white" style="background-color:{{ $themeColor }}" data-server-quantity="${i.id}" data-quantity="${i.quantity+1}">+</button></div></div>`).join(''):'<p class="py-8 text-center text-slate-500">Seu carrinho está vazio.</p>';
        summary.innerHTML=items.length?`<p class="mt-4 font-bold">Total: ${money(serverTotals?.total)}</p>`:'';
        document.querySelectorAll('[data-cart-count]').forEach(el=>el.textContent=serverTotals?.items_count||0);
    }
    async function loadCustomerData() {
        const [me,cart,addressData,orderData]=await Promise.all([api(`/api/store/${slug}/customers/me`),api(`/api/customer/${slug}/cart`),api(`/api/customer/${slug}/addresses`),api(`/api/customer/${slug}/orders`)]);
        customer=me.customer; serverCart=cart.cart; serverTotals=cart.totals; addresses=addressData.data||[]; orders=orderData.data||[]; paintAccount();
    }
    async function syncGuestCart() {
        const guest=JSON.parse(localStorage.getItem(guestKey)||'[]');
        for(const item of guest) await api(`/api/customer/${slug}/cart/items`,{method:'POST',body:JSON.stringify({product_id:item.id,quantity:item.quantity})});
        localStorage.removeItem(guestKey);
    }
    async function authenticated(data) { token=data.token; customer=data.customer; localStorage.setItem(tokenKey,token); await syncGuestCart(); await loadCustomerData(); notify(message,'Login realizado com sucesso.'); }

    document.addEventListener('click', async event => {
        if(event.target.closest('[data-open-account]')) { accountDrawer.hidden=false; cartDrawer.hidden=true; }
        if(event.target.closest('[data-close-account]')||event.target===accountDrawer) accountDrawer.hidden=true;
        const authTab=event.target.closest('[data-auth-tab]'); if(authTab){ const login=authTab.dataset.authTab==='login'; document.querySelector('[data-login-form]').hidden=!login;document.querySelector('[data-register-form]').hidden=login;document.querySelectorAll('[data-auth-tab]').forEach(b=>{b.classList.toggle('bg-white',b===authTab);b.classList.toggle('shadow-sm',b===authTab)}); }
        const accountTab=event.target.closest('[data-account-tab]'); if(accountTab){const showOrders=accountTab.dataset.accountTab==='orders';document.querySelector('[data-orders-panel]').hidden=!showOrders;document.querySelector('[data-addresses-panel]').hidden=showOrders;}
        if(event.target.closest('[data-show-address-form]')) document.querySelector('[data-address-form]').hidden=false;
        if(event.target.closest('[data-customer-logout]')) { try{await api(`/api/store/${slug}/customers/logout`,{method:'POST',body:'{}'});}catch{} token=null;customer=null;localStorage.removeItem(tokenKey);paintAccount();notify(message,'Você saiu da sua conta.'); }
    });
    document.addEventListener('click', async event => {
        if(!customer)return; const add=event.target.closest('[data-add-cart]'); if(add){event.preventDefault();event.stopImmediatePropagation();try{const data=await api(`/api/customer/${slug}/cart/items`,{method:'POST',body:JSON.stringify({product_id:Number(add.dataset.productId),quantity:1})});serverCart=data.cart;serverTotals=data.totals;renderServerCart();cartDrawer.hidden=false;}catch(e){notify(cartMessage,e.message,true)}}
        const quantity=event.target.closest('[data-server-quantity]');if(quantity){event.preventDefault();event.stopImmediatePropagation();try{const data=await api(`/api/customer/${slug}/cart/items/${quantity.dataset.serverQuantity}`,{method:'PUT',body:JSON.stringify({quantity:Number(quantity.dataset.quantity)})});serverCart=data.cart;serverTotals=data.totals;renderServerCart();}catch(e){notify(cartMessage,e.message,true)}}
    },true);
    document.querySelector('[data-login-form]')?.addEventListener('submit',async event=>{event.preventDefault();try{await authenticated(await api(`/api/store/${slug}/customers/login`,{method:'POST',body:JSON.stringify(Object.fromEntries(new FormData(event.target)))}));}catch(e){notify(message,e.message,true)}});
    document.querySelector('[data-register-form]')?.addEventListener('submit',async event=>{event.preventDefault();try{await authenticated(await api(`/api/store/${slug}/customers/register`,{method:'POST',body:JSON.stringify(Object.fromEntries(new FormData(event.target)))}));}catch(e){notify(message,e.message,true)}});
    document.querySelector('[data-address-form]')?.addEventListener('submit',async event=>{event.preventDefault();const payload=Object.fromEntries(new FormData(event.target));payload.is_default=payload.is_default==='1';try{await api(`/api/customer/${slug}/addresses`,{method:'POST',body:JSON.stringify(payload)});addresses=(await api(`/api/customer/${slug}/addresses`)).data||[];renderAddresses();event.target.reset();event.target.hidden=true;notify(message,'Endereço salvo.');}catch(e){notify(message,e.message,true)}});
    document.querySelector('[data-checkout-form]')?.addEventListener('submit',async event=>{event.preventDefault();try{const order=(await api(`/api/customer/${slug}/orders/checkout`,{method:'POST',body:JSON.stringify(Object.fromEntries(new FormData(event.target)))})).data;notify(cartMessage,`Pedido ${order.order_number||''} criado com sucesso.`);const cart=await api(`/api/customer/${slug}/cart`);serverCart=cart.cart;serverTotals=cart.totals;orders=(await api(`/api/customer/${slug}/orders`)).data||[];renderServerCart();renderOrders();}catch(e){notify(cartMessage,e.message,true)}});
    document.querySelector('[data-register-zip]')?.addEventListener('blur',async event=>{if(event.target.value.replace(/\D/g,'').length!==8)return;try{const data=await api(`/api/store/${slug}/zipcode?zip=${encodeURIComponent(event.target.value)}`);const form=event.target.form;form.street.value=data.street||data.logradouro||'';form.neighborhood.value=data.neighborhood||data.bairro||'';form.city.value=data.city||data.localidade||'';form.state.value=data.state||data.uf||'';}catch{}});
    if(token) loadCustomerData().catch(()=>{token=null;customer=null;localStorage.removeItem(tokenKey);paintAccount()}); else paintAccount();
})();
</script>
</body></html>
