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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_id')->constrained('locations');
            $table->foreignId('destination_id')->constrained('locations');
            $table->foreignId('airline_id')->constrained('airlines');
            $table->string('status'); // incoming, outgoing
            $table->date('flight_date')->default(now());
            $table->dateTime('departure_at')->nullable();
            $table->dateTime('arrival_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
