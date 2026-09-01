<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('catalog_mode', 30)->default('store')->after('type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('conversion_type', 30)->default('cart')->after('allow_whatsapp');
            $table->text('external_url')->nullable()->after('conversion_type');
            $table->string('cta_label', 80)->nullable()->after('external_url');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['conversion_type', 'external_url', 'cta_label']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('catalog_mode');
        });
    }
};
