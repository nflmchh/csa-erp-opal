<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions');
            $table->decimal('refund_amount', 15, 2)->default(0);
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->decimal('refund_amount', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            $table->dropForeign(['cash_session_id']);
            $table->dropColumn('cash_session_id');
            $table->dropColumn('refund_amount');
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn('refund_amount');
        });
    }
};
