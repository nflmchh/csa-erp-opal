<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Tambah dropdown jenis pengeluaran
            $table->string('expense_type')->after('description');
            // Tambah kolom path struk (nullable karena opsional)
            $table->string('receipt_path')->nullable()->after('amount');
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['expense_type', 'receipt_path']);
        });
    }
};