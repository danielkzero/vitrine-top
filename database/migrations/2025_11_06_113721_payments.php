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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_id')->nullable(); // id do gateway
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BRL');
            $table->enum('method', ['pix', 'credit_card', 'boleto']);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->json('details')->nullable(); // payload da API do pagamento
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
