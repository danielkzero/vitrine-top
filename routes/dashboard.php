<?php

use App\Http\Controllers\Dashboard\BaseController;
use App\Http\Controllers\Dashboard\BillingController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Middleware\EnsureAccountIsInGoodStanding;
use Illuminate\Support\Facades\Route;

Route::get('/painel', function () {
    return redirect()->route('painel.index');
})->middleware(['auth', 'verified'])->name('painel');

Route::middleware(['auth', 'verified'])
    ->prefix('painel')
    ->name('painel.')
    ->group(function () {
        Route::get('/cobranca', [BillingController::class, 'index'])->name('billing.index');
        Route::get('/assinatura', [BillingController::class, 'required'])->name('billing.required');
        Route::post('/assinatura/plano', [BillingController::class, 'choosePlan'])->name('billing.plan');
        Route::post('/assinatura/pagar', [BillingController::class, 'pay'])->name('billing.pay');
        Route::delete('/assinatura/conta', [BillingController::class, 'destroyAccount'])->name('billing.destroy-account');

        Route::middleware([EnsureAccountIsInGoodStanding::class])->group(function () {
            Route::get('/', [BaseController::class, 'index'])->name('index');

            Route::resource('categories', CategoryController::class);
            Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
            Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('payments', PaymentController::class)->only(['index', 'show', 'store']);
        });
    });

Route::prefix('painel/pages')
    ->middleware(['auth', 'verified', EnsureAccountIsInGoodStanding::class])
    ->name('painel.pages.')
    ->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index');
        Route::get('/create', [PageController::class, 'create'])->name('create');
        Route::post('/', [PageController::class, 'store'])->name('store');
        Route::get('/edit/{key}', [PageController::class, 'edit'])->name('edit');
        Route::post('/update/{key}', [PageController::class, 'update'])->name('update');
        Route::post('/reorder', [PageController::class, 'reorder'])->name('reorder');
        Route::delete('/{page}', [PageController::class, 'destroy'])->name('destroy');
    });
