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
        Schema::create('fk_music_score_style_music', function (Blueprint $table) {
            $table->id();
            $table->foreignId('music_scores_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('style_musics_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fk_music_score_style_music');
    }
};
