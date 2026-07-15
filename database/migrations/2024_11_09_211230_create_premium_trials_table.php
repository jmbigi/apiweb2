<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('premium_trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('used_count')->default(0); // Número de veces que el usuario ha utilizado la prueba
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('premium_trials');
    }
};
