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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->timestamp('deposit_at');
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users');
            $table->decimal('nominal', 15, 2);
            $table->foreignId('company_id')->constrained('companies');
            $table->string('status')->default('submit');
            $table->string('photo');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
