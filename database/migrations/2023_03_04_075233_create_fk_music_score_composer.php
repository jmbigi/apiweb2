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
        Schema::create('fk_music_score_composer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('music_scores_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('composers_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    
};
