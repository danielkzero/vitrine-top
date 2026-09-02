<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('is_active');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('custom_plan_name')->nullable()->after('plan_id');
            $table->unsignedInteger('custom_products_limit')->nullable()->after('custom_plan_name');
            $table->unsignedInteger('custom_product_images_limit')->nullable()->after('custom_products_limit');
            $table->unsignedInteger('custom_gallery_images_limit')->nullable()->after('custom_product_images_limit');
            $table->unsignedInteger('custom_banners_limit')->nullable()->after('custom_gallery_images_limit');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'custom_plan_name',
                'custom_products_limit',
                'custom_product_images_limit',
                'custom_gallery_images_limit',
                'custom_banners_limit',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
