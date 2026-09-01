<?php

namespace App\Providers;

use App\Contracts\Payments\PaymentGateway;
use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Listeners\SendOrderCreatedCustomerEmail;
use App\Listeners\SendOrderCreatedStoreEmail;
use App\Listeners\SendOrderStatusUpdatedCustomerEmail;
use App\Services\Payments\MercadoPagoGateway;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, MercadoPagoGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(OrderCreated::class, SendOrderCreatedCustomerEmail::class);
        Event::listen(OrderCreated::class, SendOrderCreatedStoreEmail::class);
        Event::listen(OrderStatusUpdated::class, SendOrderStatusUpdatedCustomerEmail::class);
    }
}
