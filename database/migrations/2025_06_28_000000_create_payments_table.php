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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->comment('ID Perusahaan yang melakukan pembayaran');
            $table->foreignId('invoice_id')->constrained('invoices')->comment('ID Invoice yang dibayar');
            $table->decimal('amount', 15, 2)->comment('Jumlah pembayaran');
            $table->string('payment_method')->default('deposit')->comment('Metode pembayaran: deposit, cash, transfer, etc');
            $table->string('status')->default('completed')->comment('Status pembayaran: pending, completed, failed');
            $table->text('description')->nullable()->comment('Keterangan pembayaran');
            $table->foreignId('created_by_user_id')->constrained('users')->comment('ID User yang mencatat pembayaran');
            $table->timestamp('paid_at')->comment('Waktu pembayaran dilakukan');
            $table->timestamps();

            // Index untuk performa query
            $table->index(['company_id', 'paid_at']);
            $table->index(['invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
