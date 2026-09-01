<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('orders', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'shipping_address_id')) {
                $table->foreignId('shipping_address_id')->nullable()->after('customer_id')->constrained('customer_addresses')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total');
            }
            if (!Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('shipping_method');
            }
            if (!Schema::hasColumn('orders', 'address_snapshot')) {
                $table->json('address_snapshot')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'placed_at')) {
                $table->timestamp('placed_at')->nullable()->after('address_snapshot');
            }
            if (!Schema::hasColumn('orders', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('placed_at');
            }
        });

        DB::table('orders')
            ->whereNull('order_number')
            ->orderBy('id')
            ->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    $number = sprintf('ORD-%06d-%s', $order->id, strtoupper(Str::random(4)));
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update([
                            'order_number' => $number,
                            'placed_at' => $order->created_at ?? now(),
                            'subtotal' => $order->total ?? 0,
                        ]);
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_number');
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'order_number')) {
                $table->dropUnique(['order_number']);
            }
            $table->dropIndex(['user_id', 'status', 'created_at']);

            foreach (['customer_id', 'shipping_address_id'] as $fk) {
                if (Schema::hasColumn('orders', $fk)) {
                    $table->dropForeign([$fk]);
                }
            }

            $columns = [
                'order_number',
                'customer_id',
                'shipping_address_id',
                'subtotal',
                'payment_method',
                'shipping_method',
                'notes',
                'address_snapshot',
                'placed_at',
                'canceled_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

