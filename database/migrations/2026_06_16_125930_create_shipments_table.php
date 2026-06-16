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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('courier_name'); // e.g. JNE, JNT, Sicepat
            $table->string('courier_service'); // e.g. REG, YES
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('pending'); // pending, pickup_scheduled, shipped, delivered
            $table->string('biteship_order_id')->nullable();
            $table->string('biteship_tracking_id')->nullable();
            $table->json('shipping_address'); // Snapshot of address
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
