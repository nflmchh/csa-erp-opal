<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('notes');
            $table->string('customer_phone')->nullable()->after('customer_name');
            // 'system' = harga sistem (ecer), 'grosir' = harga grosir dari DB, 'custom' = input kasir
            $table->string('price_method')->default('system')->after('customer_phone');
            // 'lunas' = full payment, 'tempo' = partial/DP/PO
            $table->string('payment_status')->default('lunas')->after('price_method');
            $table->decimal('dp_amount', 15, 2)->default(0)->after('payment_status'); // jumlah DP jika tempo
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'price_method', 'payment_status', 'dp_amount']);
        });
    }
};
