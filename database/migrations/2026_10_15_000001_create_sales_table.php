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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique(); // INV-YYYYMMDD-0001
            $table->foreignId('user_id')->constrained(); // kasir yang bertransaksi
            $table->string('customer_name')->nullable();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->decimal('paid', 14, 2);
            $table->decimal('change', 14, 2)->default(0);
            $table->string('payment_method', 20); // cash | qris | debit
            $table->string('status', 10)->default('paid'); // paid | void
            $table->timestamps();

            $table->index('created_at'); // dasar query laporan & dashboard
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
