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
        Schema::create('fk_instrument_family_instrument', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('family_instrument_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fk_instrument_family_instrument');
    }
};
