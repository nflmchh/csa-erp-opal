<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->integer('qty_sent');
            $table->integer('qty_received')->default(0);
            $table->timestamps();

            $table->unique(['shipment_id', 'product_variant_id'], 'uq_shipment_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
