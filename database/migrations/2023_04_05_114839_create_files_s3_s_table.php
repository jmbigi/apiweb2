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
        Schema::create('files_s3_s', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('storagePlace')->default('Wasabi');
            $table->string('extension')->nullable(); //pdf, png, etc
            $table->unsignedBigInteger('fileable_id');
            $table->string('fileable_type');
            $table->timestamps();
        });
    }

    
};
