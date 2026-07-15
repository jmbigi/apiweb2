<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ensemble_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ensemble_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default('usuario');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign('ensemble_id')->references('id')->on('ensembles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['ensemble_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ensemble_user');
    }
};
