<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('hold_id');
    $table->unsignedBigInteger('product_id');
    $table->integer('qty');
    $table->string('payment_reference')->unique();
    $table->string('status')->default('pending');
    $table->decimal('total_amount', 10, 2);
    $table->timestamps();

    $table->foreign('hold_id')->references('id')->on('holds')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});

    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
