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
        Schema::create('link_infos', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('social_network')->nullable();
            $table->foreignId('music_scores_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    
};
