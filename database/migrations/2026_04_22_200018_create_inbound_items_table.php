<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->integer('qty');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['inbound_id', 'product_variant_id'], 'uq_inbound_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_items');
    }
};
