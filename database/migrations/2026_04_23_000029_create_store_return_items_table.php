<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained();
            $table->unsignedInteger('qty_returned');
            $table->unsignedInteger('qty_good')->default(0);
            $table->unsignedInteger('qty_damaged')->default(0);
            $table->text('item_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_return_items');
    }
};
