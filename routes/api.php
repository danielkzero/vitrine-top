<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{
    UserController,
    SettingsController,
    PageController,
    ProductController,
    CategoryController,
    BannerController,
    ReviewController
};

Route::prefix('v1')->group(function () {

    // User Info
    Route::get('users/{slug}', [UserController::class, 'show']);

    // Settings
    Route::get('users/{slug}/settings', [SettingsController::class, 'index']);

    // Pages
    Route::get('users/{slug}/pages', [PageController::class, 'index']);

    // Products
    Route::get('users/{slug}/products', [ProductController::class, 'index']);
    Route::get('users/{slug}/products/{id}', [ProductController::class, 'show']);

    // Categories
    Route::get('users/{slug}/categories', [CategoryController::class, 'index']);

    // Banners
    Route::get('users/{slug}/banners', [BannerController::class, 'index']);

    // Reviews
    Route::get('users/{slug}/reviews', [ReviewController::class, 'index']);
});
