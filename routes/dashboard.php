<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\BaseController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\PaymentController;
use Inertia\Inertia;

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])
    ->prefix('painel')
    ->name('painel.')
    ->group(function () {
        Route::get('/', [BaseController::class, 'index'])->name('index');

        Route::resource('categories', CategoryController::class);
        Route::resource('pages', PageController::class)->except(['show']);
        Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
        Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('payments', PaymentController::class)->only(['index', 'show', 'store']);
    });
