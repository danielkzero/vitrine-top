<?php

use App\Http\Controllers\Api\Admin\AnalyticsController;
use App\Http\Controllers\Api\Admin\OrderManagementController;
use App\Http\Controllers\Api\Customer\AddressController as CustomerAddressController;
use App\Http\Controllers\Api\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Api\Customer\FavoriteController as CustomerFavoriteController;
use App\Http\Controllers\Api\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Api\Store\CustomerAuthController;
use App\Http\Controllers\Api\Store\StoreCatalogController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('users/{slug}', [UserController::class, 'show'])->middleware('track.store_visit');
    Route::get('users/{slug}/settings', [SettingsController::class, 'index']);
    Route::get('users/{slug}/pages', [PageController::class, 'index'])->middleware('track.store_visit');
    Route::get('users/{slug}/products', [ProductController::class, 'index']);
    Route::get('users/{slug}/products/{id}', [ProductController::class, 'show'])->middleware('track.store_visit');
    Route::get('users/{slug}/products/{id}/reviews', [ReviewController::class, 'product']);
    Route::get('users/{slug}/categories', [CategoryController::class, 'index']);
    Route::get('users/{slug}/banners', [BannerController::class, 'index']);
    Route::get('users/{slug}/reviews', [ReviewController::class, 'index']);
});

Route::prefix('store/{storeSlug}')->group(function () {
    Route::get('info', [StoreCatalogController::class, 'info']);
    Route::get('pages', [StoreCatalogController::class, 'pages'])->middleware('track.store_visit');
    Route::get('pages/{pageKey}', [StoreCatalogController::class, 'page'])->middleware('track.store_visit');
    Route::get('products', [StoreCatalogController::class, 'products']);
    Route::get('products/{productId}', [StoreCatalogController::class, 'product'])->middleware('track.store_visit');

    Route::get('zipcode', [CustomerAuthController::class, 'lookupZip'])->middleware('throttle:30,1');
    Route::post('customers/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('customers/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth.customer_token', 'customer.store_context'])->group(function () {
        Route::post('customers/logout', [CustomerAuthController::class, 'logout']);
        Route::get('customers/me', [CustomerAuthController::class, 'me']);
    });
});

Route::prefix('customer/{storeSlug}')
    ->middleware(['auth.customer_token', 'customer.store_context'])
    ->group(function () {
        Route::get('addresses', [CustomerAddressController::class, 'index']);
        Route::post('addresses', [CustomerAddressController::class, 'store']);
        Route::put('addresses/{addressId}', [CustomerAddressController::class, 'update']);
        Route::delete('addresses/{addressId}', [CustomerAddressController::class, 'destroy']);

        Route::get('cart', [CustomerCartController::class, 'show']);
        Route::post('cart/items', [CustomerCartController::class, 'addItem']);
        Route::put('cart/items/{itemId}', [CustomerCartController::class, 'updateItem']);
        Route::delete('cart/items/{itemId}', [CustomerCartController::class, 'removeItem']);
        Route::delete('cart', [CustomerCartController::class, 'clear']);

        Route::get('orders', [CustomerOrderController::class, 'index']);
        Route::get('orders/items/latest', [CustomerOrderController::class, 'latestItems']);
        Route::get('orders/{orderId}', [CustomerOrderController::class, 'show']);
        Route::post('orders/checkout', [CustomerOrderController::class, 'checkout']);
        Route::post('orders/{orderId}/repeat', [CustomerOrderController::class, 'repeat']);
        Route::get('orders/{orderId}/whatsapp-link', [CustomerOrderController::class, 'whatsappLink']);

        Route::get('favorites', [CustomerFavoriteController::class, 'index']);
        Route::post('favorites', [CustomerFavoriteController::class, 'store']);
        Route::delete('favorites/{productId}', [CustomerFavoriteController::class, 'destroy']);
    });

Route::prefix('admin')
    ->middleware(['web', 'auth', 'auth.api_admin'])
    ->group(function () {
        Route::get('orders', [OrderManagementController::class, 'index']);
        Route::get('orders/{orderId}', [OrderManagementController::class, 'show']);
        Route::patch('orders/{orderId}/status', [OrderManagementController::class, 'updateStatus']);

        Route::get('analytics/summary', [AnalyticsController::class, 'summary']);
        Route::get('analytics/orders-by-period', [AnalyticsController::class, 'ordersByPeriod']);
    });
