<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ensemble_folders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ensemble_id');
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            $table->foreign('ensemble_id')->references('id')->on('ensembles')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('ensemble_folders')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ensemble_folders');
    }
};
