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
        Schema::create('warehouse_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('admin_fee', 15, 2)->default(0)->comment('Biaya admin dalam decimal');
            $table->decimal('tax', 5, 2)->default(0)->comment('Pajak dalam persentase (contoh: 11.00 untuk 11%)');
            $table->decimal('pnbp', 15, 2)->default(0)->comment('PNBP dalam rupiah');
            $table->integer('minimal_charge_weight')->default(0)->comment('Minimal charge dalam satuan kg');
            $table->decimal('max_negative_balance', 15, 2)->default(0)->comment('Maksimal minus saldo yang diperbolehkan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_settings');
    }
};
