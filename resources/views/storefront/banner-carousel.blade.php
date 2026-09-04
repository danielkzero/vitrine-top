<div data-banner-carousel class="relative {{ $class ?? '' }}">
    <div class="flex h-full transition-transform duration-500 ease-out" data-banner-track>
        @foreach($banners as $banner)
            <div class="h-full min-w-full" data-banner-slide>
                <img class="h-full w-full object-cover" src="{{ $assetUrl($banner->image_url) }}" alt="{{ $banner->title ?: 'Banner de '.$store->business_name }}">
            </div>
        @endforeach
    </div>
    @if($banners->count() > 1)
        <div class="absolute inset-x-0 bottom-3 flex justify-center gap-1.5">
            @foreach($banners as $banner)<button type="button" data-banner-dot="{{ $loop->index }}" aria-label="Mostrar banner {{ $loop->iteration }}" class="h-1.5 rounded-full bg-white/60 shadow transition-all {{ $loop->first ? 'w-4 bg-white' : 'w-1.5' }}"></button>@endforeach
        </div>
    @endif
</div>
