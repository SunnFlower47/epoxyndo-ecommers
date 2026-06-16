<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->json('name');                        // Bilingual { "id": "...", "en": "..." }
            $table->string('slug')->unique();
            $table->json('description')->nullable();     // Bilingual
            $table->string('sku')->unique();
            $table->decimal('price', 15, 2);            // Harga normal
            $table->decimal('discount_value', 15, 2)->nullable(); // Nilai diskon
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable(); // Tipe diskon
            $table->timestamp('discount_start')->nullable(); // Flash sale: waktu mulai
            $table->timestamp('discount_end')->nullable();   // Flash sale: waktu selesai
            $table->integer('stock')->default(0);
            $table->decimal('weight', 8, 2)->default(0); // Berat dalam gram
            $table->integer('moq')->default(1);          // Minimum Order Quantity
            $table->boolean('is_bulky')->default(false); // Produk besar / perlu handling khusus
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
