@php $currentPrice=$product->discount_price>0?$product->discount_price:$product->price; $mainImage=$product->images->first(); @endphp
<div class="mx-auto min-h-screen w-full max-w-5xl bg-white md:min-h-0 md:rounded-2xl md:border md:border-slate-200/80 md:shadow-sm">
    <header class="grid grid-cols-[2.5rem_1fr_2.5rem] items-center border-b border-slate-100 px-3 py-3">
        <button type="button" onclick="history.back()" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm" aria-label="Voltar">@include('storefront.icon', ['name'=>'ArrowLeft','attributes'=>'class="h-5 w-5"'])</button>
        <h1 class="text-center text-sm font-semibold text-slate-900">Detalhes do Produto</h1>
        <button type="button" data-toggle-favorite class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-rose-400 shadow-sm" aria-label="Favoritar">@include('storefront.icon', ['name'=>'Heart','attributes'=>'class="h-5 w-5"'])</button>
    </header>

    <section class="md:grid md:grid-cols-[1.1fr_.9fr] md:gap-6 md:p-6">
        <div class="px-4 pt-4 md:px-0 md:pt-0">
            <div class="relative flex h-[17rem] w-full items-center justify-center bg-white md:h-[30rem] md:rounded-2xl md:bg-slate-50">@if($mainImage)<img data-product-main src="{{ $assetUrl($mainImage->image_path) }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">@endif<span class="absolute bottom-3 h-2.5 w-2.5 rounded-full" style="background-color:{{ $themeColor }}"></span></div>
            @if($product->images->isNotEmpty())<div class="mt-3 flex gap-2 overflow-x-auto">@foreach($product->images as $image)<button type="button" class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border bg-white p-1" @if($loop->first) style="border-color:{{ $themeColor }}" @endif><img data-product-thumb src="{{ $assetUrl($image->image_path) }}" alt="{{ $product->name }} — imagem {{ $loop->iteration }}" class="h-full w-full object-cover"></button>@endforeach</div>@endif
        </div>

        <div class="px-4 pt-7 pb-5 md:px-0 md:pt-0">
            <div class="flex items-start justify-between gap-4"><h2 class="text-lg font-bold leading-snug text-slate-950 md:text-2xl">{{ $product->name }}</h2><div class="shrink-0 text-right">@if($product->discount_price>0)<div class="text-xs text-slate-400 line-through">R$ {{ number_format((float)$product->price,2,',','.') }}</div>@endif<div class="text-xl font-bold" style="color:{{ $themeColor }}">R$ {{ number_format((float)$currentPrice,2,',','.') }}</div></div></div>
            @if($product->category)<p class="mt-4 text-sm text-slate-600">{{ $product->category->name }}</p>@endif
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $product->description }}</p>
            <div class="mt-3 flex items-center gap-3 text-xs text-slate-500"><span>Estoque: {{ $product->stock }}</span>@if($product->approved_reviews_count)<span class="inline-flex items-center gap-1"><span class="text-amber-400">★</span>{{ number_format((float)$product->approved_rating,1,',','.') }}</span>@endif</div>
            <div class="mt-4 flex gap-2">
                @if(($product->conversion_type?:'cart')==='external'&&$product->external_url)<a class="flex-1 rounded-xl py-3 text-center font-semibold text-white" style="background-color:{{ $themeColor }}" href="{{ $product->external_url }}" target="_blank" rel="nofollow sponsored noopener">{{ $product->cta_label?:'Ver oferta' }}</a>
                @elseif(($product->conversion_type?:'cart')==='whatsapp')<a class="flex-1 rounded-xl py-3 text-center font-semibold text-white" style="background-color:{{ $themeColor }}" href="https://wa.me/{{ $phone }}?text={{ urlencode('Olá, tenho interesse em '.$product->name) }}" target="_blank" rel="noopener">{{ $product->cta_label?:'Comprar via WhatsApp' }}</a>
                @else<button class="flex-1 rounded-xl py-3 font-semibold text-white" style="background-color:{{ $themeColor }}" type="button" data-add-cart data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-price="{{ (float)$currentPrice }}" {{ $product->stock<=0?'disabled':'' }}>{{ $product->cta_label?:'Adicionar ao carrinho' }}</button>@endif
                <button type="button" data-open-cart class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50" aria-label="Abrir carrinho" title="Abrir carrinho">@include('storefront.icon', ['name'=>'ShoppingCart','attributes'=>'class="h-5 w-5"'])</button>
            </div>
        </div>
    </section>

    <section class="px-4 pb-8 md:px-6">
        <div class="mb-3 flex gap-2"><span class="rounded-full bg-slate-100 px-4 py-2 text-sm">Avaliações ({{ $product->reviews->count() }})</span><button type="button" data-toggle-review class="inline-flex items-center gap-2 rounded-full bg-slate-600 px-4 py-2 text-sm font-semibold text-white">@include('storefront.icon', ['name'=>'UserStar','attributes'=>'class="h-5 w-5"'])<span data-review-toggle-label>Avaliar</span></button></div>

        <form data-review-form hidden method="post" action="{{ route('vitrine.reviews.store',[$store->slug,$product->id]) }}" class="mb-4 rounded-xl border bg-slate-50 p-4">@csrf
            <input class="mb-2 w-full rounded-xl border bg-transparent p-3" aria-label="Nome" placeholder="Seu nome" name="customer_name" value="{{ old('customer_name') }}" required maxlength="255">
            <input class="mb-2 w-full rounded-xl border bg-transparent p-3" aria-label="WhatsApp" placeholder="Seu WhatsApp" name="whatsapp" value="{{ old('whatsapp') }}" maxlength="20">
            <fieldset><legend class="sr-only">Nota</legend><div class="flex gap-1">@for($rating=1;$rating<=5;$rating++)<label data-rating-star data-value="{{ $rating }}" class="cursor-pointer text-slate-300 transition hover:scale-110 hover:text-amber-400"><input class="sr-only" type="radio" name="rating" value="{{ $rating }}" data-rating-input @checked(old('rating')==$rating) required><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.7 5.5 6 .9-4.4 4.2 1 6-5.3-2.8-5.3 2.8 1-6-4.4-4.2 6-.9L12 3Z"/></svg></label>@endfor<span class="sr-only" data-rating-label>Selecione</span></div></fieldset>
            <textarea class="mt-3 w-full rounded-xl border bg-transparent p-3" aria-label="Comentário" placeholder="Comentário" name="comment" rows="4" required maxlength="2000">{{ old('comment') }}</textarea>
            <button class="mt-3 w-full rounded-xl py-3 font-semibold text-white" style="background-color:{{ $themeColor }}" type="submit">Enviar Avaliação</button>
        </form>

        <div class="space-y-3">@forelse($product->reviews as $review)<article class="rounded-xl bg-slate-50 p-3"><div class="flex justify-between gap-3"><strong class="text-sm">{{ $review->customer_name }}</strong><small class="text-slate-400">{{ $review->created_at->format('d/m/Y') }}</small></div><div class="mt-1 text-amber-400" aria-label="Nota {{ $review->rating }} de 5">{{ str_repeat('☆',$review->rating) }}<span class="text-slate-300">{{ str_repeat('☆',5-$review->rating) }}</span></div><p class="mt-1 text-sm leading-relaxed text-slate-700">{{ $review->comment }}</p></article>@empty<p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Ainda não há avaliações publicadas para este produto.</p>@endforelse</div>
    </section>
</div>
