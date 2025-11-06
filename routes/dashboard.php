<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\BaseController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\PaymentController;

Route::middleware(['auth', 'verified'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard principal
        |--------------------------------------------------------------------------
        */
        Route::get('/', [BaseController::class, 'index'])->name('index');

        /*
        |--------------------------------------------------------------------------
        | Categorias
        |--------------------------------------------------------------------------
        */
        Route::resource('categories', CategoryController::class);

        /*
        |--------------------------------------------------------------------------
        | Páginas
        |--------------------------------------------------------------------------
        */
        Route::resource('pages', PageController::class)
            ->except(['show']); // exibição pública fora do dashboard

        /*
        |--------------------------------------------------------------------------
        | Avaliações (Reviews)
        |--------------------------------------------------------------------------
        */
        Route::resource('reviews', ReviewController::class)
            ->only(['index', 'update', 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | Assinaturas (Subscriptions)
        |--------------------------------------------------------------------------
        */
        Route::resource('subscriptions', SubscriptionController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | Pagamentos (Payments)
        |--------------------------------------------------------------------------
        */
        Route::resource('payments', PaymentController::class)
            ->only(['index', 'show', 'store']);
    });
