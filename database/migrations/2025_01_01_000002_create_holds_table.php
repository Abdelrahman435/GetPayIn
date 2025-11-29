<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index('expires_at');
            $table->index(['product_id', 'used']);
            $table->unique(['id', 'used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
