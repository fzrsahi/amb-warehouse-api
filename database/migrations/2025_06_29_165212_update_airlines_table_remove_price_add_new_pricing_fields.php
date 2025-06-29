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
        Schema::table('airlines', function (Blueprint $table) {
            // Remove the old price field
            $table->dropColumn('price');

            // Add new pricing fields
            $table->decimal('cargo_handling_incoming_price', 15, 2)->default(0);
            $table->decimal('cargo_handling_outgoing_price', 15, 2)->default(0);
            $table->decimal('handling_airplane_outgoing_price', 15, 2)->default(0);
            $table->decimal('handling_airplane_incoming_price', 15, 2)->default(0);
            $table->decimal('jppgc_incoming_price', 15, 2)->default(0);
            $table->decimal('jppgc_outgoing_price', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            // Remove new pricing fields
            $table->dropColumn([
                'cargo_handling_incoming_price',
                'cargo_handling_outgoing_price',
                'handling_airplane_outgoing_price',
                'handling_airplane_incoming_price',
                'jppgc_incoming_price',
                'jppgc_outgoing_price'
            ]);

            // Add back the old price field
            $table->decimal('price', 15, 2)->default(0);
        });
    }
};
