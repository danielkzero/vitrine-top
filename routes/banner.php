<?php

use App\Http\Controllers\Dashboard\BannerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')
    ->prefix('painel')
    ->group(function () {
        Route::get('/banners', [BannerController::class, 'index'])
            ->name('painel.banners.index');

        Route::post('/banners', [BannerController::class, 'store'])
            ->name('painel.banners.store');

        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])
            ->name('painel.banners.destroy');

        Route::post('/banners/reorder', [BannerController::class, 'reorder'])
            ->name('painel.banners.reorder');
    });
