<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_hash', 64);
            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
