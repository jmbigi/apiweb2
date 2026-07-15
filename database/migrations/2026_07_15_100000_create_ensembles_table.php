<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ensembles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('cif', 20)->unique()->comment('CIF/NIF de la agrupación');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('owner_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ensembles');
    }
};
