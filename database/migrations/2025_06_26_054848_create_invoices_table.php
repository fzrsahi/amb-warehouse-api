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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Sesuai dengan No. KUITANSI -> GTOPOD25060000044J
            $table->string('invoice_number')->unique()->comment('Nomor unik kuitansi/invoice.');

            // Sesuai dengan Telah Terima Dari -> KOTAG PUTRA GORONTALO
            $table->foreignId('company_id')->constrained('companies')->comment('ID Perusahaan/Mitra yang ditagih.');

            // Sesuai dengan Petugas -> Moh. Zulfikar Amrain
            $table->foreignId('created_by_user_id')->constrained('users')->comment('ID Petugas yang membuat invoice.');

            // Sesuai dengan keterangan -> Chargeable Weight: 157 kg
            $table->decimal('total_chargeable_weight', 10, 2)->comment('Akumulasi berat yang ditagih dari semua item.');

            // Kolom untuk setiap komponen biaya
            $table->decimal('cargo_handling_fee', 15, 2)->default(0.00);
            $table->decimal('air_handling_fee', 15, 2)->default(0.00)->comment('Biaya Handling Udara/Uap Air.');
            $table->decimal('inspection_fee', 15, 2)->default(0.00)->comment('Jasa Pemeriksaan Kargo.');
            $table->decimal('admin_fee', 15, 2)->default(0.00);

            // Kolom untuk total perhitungan
            $table->decimal('subtotal', 15, 2)->comment('Total biaya sebelum pajak dan PNBP.');
            $table->decimal('tax_amount', 15, 2)->comment('Jumlah Pajak (PPN 11%).');
            $table->decimal('pnbp_amount', 15, 2)->comment('Jumlah PNBP.');
            $table->decimal('total_amount', 15, 2)->comment('Jumlah akhir tagihan yang harus dibayar.');

            // Status pembayaran invoice
            $table->enum('status', ['draft', 'unpaid', 'paid', 'void'])->default('unpaid');

            // Status approval invoice
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');

            // Kolom untuk approval
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->comment('ID User yang menyetujui invoice.');
            $table->timestamp('approved_at')->nullable()->comment('Waktu invoice disetujui.');
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->comment('ID User yang menolak invoice.');
            $table->timestamp('rejected_at')->nullable()->comment('Waktu invoice ditolak.');

            // Sesuai dengan tanggal di kanan bawah -> 05 June 2025
            $table->date('issued_at')->comment('Tanggal invoice diterbitkan.');
            $table->timestamp('paid_at')->nullable()->comment('Tanggal invoice dibayar.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};