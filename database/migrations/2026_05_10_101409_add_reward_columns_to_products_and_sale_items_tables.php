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
        // 1. Tambah kolom di tabel products
        // Schema::table('products', function (Blueprint $table) {
        //     $table->integer('reward_store')->default(500)->after('sell_price');
        //     $table->integer('reward_owner')->default(4500)->after('reward_store');
        // });

        // 2. Tambah kolom di tabel sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            // Kolom ini akan disuntikkan saat transaksi terjadi di kasir
            $table->integer('reward_store')->default(0)->after('unit_price');
            $table->integer('reward_owner')->default(0)->after('reward_store');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fitur rollback: Menghapus kolom jika migrasi di-cancel
        // Schema::table('products', function (Blueprint $table) {
        //     $table->dropColumn(['reward_store', 'reward_owner']);
        // });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['reward_store', 'reward_owner']);
        });
    }
};