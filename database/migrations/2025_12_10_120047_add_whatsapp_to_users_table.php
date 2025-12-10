<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Armazenar em E.164 (ex: +5511999999999). 20 chars é suficiente.
            $table->string('whatsapp', 20)->nullable()->after('phone_primary');
            // Opcional: índice para buscas rápidas
            $table->index('whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['whatsapp']);
            $table->dropColumn('whatsapp');
        });
    }
};
