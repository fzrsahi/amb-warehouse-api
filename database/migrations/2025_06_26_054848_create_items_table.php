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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            // Sesuai dengan BTB/TTB No. -> GTOCOD25060000156
            $table->string('code')->unique()->nullable()->comment('Nomor Tanda Terima Barang (BTB/TTB) unik dari sistem.');

            // Sesuai dengan AWB -> 990-66429193
            $table->string('awb')->unique()->comment('Air Waybill Number dari maskapai.');

            // Sesuai dengan Customer -> KOTAG PUTRA GORONTALO
            // Menghubungkan ke tabel 'companies' (mitra)
            $table->foreignId('company_id')->constrained('companies')->comment('ID Mitra/Perusahaan Pengirim.');

            // Sesuai dengan Flight/Dest. -> JT-0891
            // Menghubungkan ke tabel 'flights'
            $table->foreignId('flight_id')->nullable()->constrained('flights')->comment('ID Penerbangan yang digunakan.');

            // Sesuai dengan Commodity -> AMPEL ISI TANGKI
            $table->string('commodity')->comment('Deskripsi atau jenis barang.');

            // Sesuai dengan Qty -> 1
            $table->integer('qty')->comment('Jumlah koli/paket.');

            // Sesuai dengan GW -> 5
            $table->decimal('gross_weight', 10, 2)->comment('Berat aktual barang dalam Kg.');

            // Sesuai dengan CW -> 10
            $table->decimal('chargeable_weight', 10, 2)->comment('Berat yang ditagihkan maskapai dalam Kg.');

            // Status item: pending_submission (by company), at_origin_warehouse (accepted by warehouse), etc.
            $table->string('status')->default('pending_submission');

            // Diisi oleh user company saat submit, atau user warehouse saat menerima
            $table->foreignId('created_by_user_id')->constrained('users')->comment('ID Petugas yang membuat data item.');

            // Diisi oleh user warehouse saat verifikasi/penerimaan
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->comment('ID Petugas yang menerima barang.');

            $table->timestamp('accepted_at')->nullable()->comment('Waktu barang diterima oleh gudang.');

            $table->decimal('length', 10, 2)->nullable()->comment('Panjang barang dalam cm.');
            $table->decimal('width', 10, 2)->nullable()->comment('Lebar barang dalam cm.');
            $table->decimal('height', 10, 2)->nullable()->comment('Tinggi barang dalam cm.');
            $table->string('weight_calculation_method');

            // Sesuai dengan Volume -> 10
            $table->decimal('volume_weight', 10, 2)->nullable()->comment('Volume barang dalam m³.');

            $table->timestamp('in_at')->nullable()->comment('Waktu barang masuk.');
            $table->timestamp('out_at')->nullable()->comment('Waktu barang keluar.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
