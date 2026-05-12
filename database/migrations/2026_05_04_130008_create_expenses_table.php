<?php

use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration
   {
       public function up()
       {
           Schema::create('expenses', function (Blueprint $table) {
               $table->id();
               $table->string('title');
               $table->text('description')->nullable();
               $table->decimal('amount', 15, 2);
               $table->date('expense_date');
               
               // Asal pengeluaran: Nullable karena bisa dari Toko ATAU Gudang
               $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
               $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
               
               $table->foreignId('created_by')->constrained('users');
               $table->timestamps();
           });
       }

       public function down()
       {
           Schema::dropIfExists('expenses');
       }
   };
