<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rehearsals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ensemble_id');
            $table->string('title');
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign('ensemble_id')->references('id')->on('ensembles')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rehearsals');
    }
};
