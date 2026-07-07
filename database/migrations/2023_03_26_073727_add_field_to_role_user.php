<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('composers', function (Blueprint $table) {
            //
            $table->dateTime('request')->nullable();
            $table->dateTime('aproved')->nullable();
        });
        Schema::table('style_musics', function (Blueprint $table) {
            //
            $table->dateTime('request')->nullable();
            $table->dateTime('aproved')->nullable();
        });
        Schema::table('instruments', function (Blueprint $table) {
            //
            $table->dateTime('request')->nullable();
            $table->dateTime('aproved')->nullable();
        });
        Schema::table('family_instruments', function (Blueprint $table) {
            //
            $table->dateTime('request')->nullable();
            $table->dateTime('aproved')->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dateTime('composer_request')->nullable();
            $table->dateTime('composer_aproved')->nullable();
        });
    }

    
};
