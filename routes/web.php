<?php

use App\Http\Controllers\Api\V1\BannerController as ApiV1BannerController;
use App\Http\Controllers\Api\V1\CategoryController as ApiV1CategoryController;
use App\Http\Controllers\Api\V1\PageController as ApiV1PageController;
use App\Http\Controllers\Api\V1\ProductController as ApiV1ProductController;
use App\Http\Controllers\Api\V1\ReviewController as ApiV1ReviewController;
use App\Http\Controllers\Api\V1\SettingsController as ApiV1SettingsController;
use App\Http\Controllers\Api\V1\UserController as ApiV1UserController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Vitrine\VitrineController;
use App\Http\Controllers\Webhook\MercadoPagoWebhookController;
use App\Http\Controllers\SitemapController;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Página inicial
Route::get('/', function () {
    $planCatalog = Plan::where('is_active', true)
        ->orderBy('monthly_price')
        ->get()
        ->map(fn (Plan $plan) => [
            'code' => $plan->code->value,
            'name' => $plan->name,
            'monthly_price' => (float) $plan->monthly_price,
            'annual_price_total' => (float) $plan->annual_price_total,
            'annual_monthly_equivalent' => (float) $plan->annual_monthly_equivalent,
            'limits' => [
                'products' => $plan->products_limit,
                'product_images' => $plan->product_images_limit,
                'gallery_images' => $plan->gallery_images_limit,
                'banners' => $plan->banners_limit,
            ],
            'trial_days' => $plan->trial_days,
        ]);

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'planCatalog' => $planCatalog,
        'trialDays' => 14,
    ]);
})->name('home');

// Endereço estável usado pelos CTAs da landing page para abrir a loja demo.
Route::redirect('/demonstracao', '/minha-lojinha/catalogo')
    ->name('demo');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response(
    "User-agent: *\nAllow: /\n\nSitemap: ".route('sitemap')."\n",
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8'],
))->name('robots');

// Público: avaliações
Route::get('/avaliacoes', [ReviewController::class, 'publicIndex'])->name('reviews.public');

// Planos
Route::get('/planos', [SubscriptionController::class, 'plans'])->name('subscriptions.plans');

// Pagamentos
Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout', [PaymentController::class, 'process'])->name('checkout.process');
Route::get('/checkout/sucesso', [PaymentController::class, 'success'])->name('checkout.success');
Route::get('/checkout/erro', [PaymentController::class, 'error'])->name('checkout.error');
Route::post('/webhooks/mercado-pago', MercadoPagoWebhookController::class)->name('payments.mercado-pago.webhook');

// Outras rotas internas
require __DIR__.'/dashboard.php';
require __DIR__.'/settings.php';
require __DIR__.'/banner.php';
require __DIR__.'/admin.php';

// Compatibilidade legada para clientes que ainda chamam /v1 sem /api.
Route::prefix('v1')->group(function () {
    Route::get('users/{slug}', [ApiV1UserController::class, 'show']);
    Route::get('users/{slug}/settings', [ApiV1SettingsController::class, 'index']);
    Route::get('users/{slug}/pages', [ApiV1PageController::class, 'index']);
    Route::get('users/{slug}/products', [ApiV1ProductController::class, 'index']);
    Route::get('users/{slug}/products/{id}', [ApiV1ProductController::class, 'show']);
    Route::get('users/{slug}/products/{id}/reviews', [ApiV1ReviewController::class, 'product']);
    Route::get('users/{slug}/categories', [ApiV1CategoryController::class, 'index']);
    Route::get('users/{slug}/banners', [ApiV1BannerController::class, 'index']);
    Route::get('users/{slug}/reviews', [ApiV1ReviewController::class, 'index']);
});

// A vitrine deve SEMPRE ser a ÚLTIMA ROTA!
Route::prefix('{slug}')->group(function () {

    Route::get('/', [VitrineController::class, 'home'])
        ->middleware('track.store_visit')
        ->name('vitrine.public.home');

    Route::get('/{page}', [VitrineController::class, 'page'])
        ->middleware('track.store_visit')
        ->name('vitrine.public.page');

    Route::get('/{page}/{id}', [VitrineController::class, 'pageWithId'])
        ->middleware('track.store_visit')
        ->whereNumber('id')
        ->name('vitrine.public.page.id');

    Route::post('/products/{product}/reviews', [VitrineController::class, 'storeReview'])
        ->name('vitrine.reviews.store');
});
