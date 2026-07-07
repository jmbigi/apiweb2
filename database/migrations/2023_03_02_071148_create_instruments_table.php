<?php

use Database\Seeders\InstrumentSeeder;
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
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('family_instruments_id');
            $table->timestamps();

            $table->foreign('family_instruments_id')->references('id')->on('family_instruments');
        });

        $seeder = new InstrumentSeeder();
        $seeder->run();
    }

    
};
