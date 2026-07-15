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
        Schema::create('log_display_music_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('music_scores_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_display_music_scores');
    }
};
