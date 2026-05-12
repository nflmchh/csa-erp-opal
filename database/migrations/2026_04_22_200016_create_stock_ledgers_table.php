<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->morphs('location'); // location_type + location_id
            $table->enum('type', ['in', 'out', 'adjust', 'transfer_in', 'transfer_out', 'sale', 'return', 'opname']);
            $table->integer('qty');        // positive = masuk, negative = keluar
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->string('reference_type')->nullable(); // model class e.g. Shipment, Transfer, Sale
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_variant_id', 'location_type', 'location_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
