<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan')->default('basic');
            $table->decimal('price', 10, 2)->default(29.90);
            $table->enum('billing_period', ['monthly', 'annual'])->default('monthly');
            $table->date('trial_ends_at')->nullable();
            $table->date('next_billing_at')->nullable();
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('trial');
            $table->string('payment_method')->nullable(); // pix, credit_card, etc.
            $table->string('gateway_subscription_id')->nullable(); // id no provedor (MercadoPago, Stripe etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
