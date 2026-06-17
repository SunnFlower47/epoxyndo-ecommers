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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->string('label');           // e.g. "1 kg", "5 kg", "25 kg"
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 15, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('weight', 10, 2)->nullable(); // gram, for shipping
            $table->boolean('is_bulky')->default(false);  // if true → cargo courier
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
