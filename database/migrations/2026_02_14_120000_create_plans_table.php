<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');

            $table->decimal('monthly_price', 10, 2);
            $table->decimal('annual_price_total', 10, 2);
            $table->decimal('annual_monthly_equivalent', 10, 2);

            $table->unsignedInteger('products_limit');
            $table->unsignedInteger('product_images_limit')->nullable();
            $table->unsignedInteger('gallery_images_limit');
            $table->unsignedInteger('banners_limit');

            $table->unsignedSmallInteger('trial_days')->default(14);

            // Campos para amarrar IDs de plano no gateway (ex.: Mercado Pago)
            $table->string('gateway_provider')->nullable();
            $table->string('gateway_plan_ref_monthly')->nullable();
            $table->string('gateway_plan_ref_annual')->nullable();
            $table->json('gateway_metadata')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

