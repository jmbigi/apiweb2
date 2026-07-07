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
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('composer_request', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('composers', function (Blueprint $table) {
            $table->softDeletes();
        });
        
    }

    
};
