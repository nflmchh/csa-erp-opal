<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->morphs('location'); // location_type + location_id (Warehouse or Store)
            $table->integer('qty')->default(0);
            $table->timestamps();

            $table->unique(['product_variant_id', 'location_type', 'location_id'], 'uq_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
