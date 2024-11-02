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
        Schema::create('music_scores', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            //relación polimorfica --> //$table->string('image');
            //relación polimorfica --> //$table->string('pdf');
            $table->text('description');
            $table->unsignedBigInteger('owner_id');

            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music_scores');
    }
};
