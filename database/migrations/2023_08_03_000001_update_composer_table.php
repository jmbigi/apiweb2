<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     //update table composer field aproved to approved
    public function up(): void
    {
        Schema::table('composers', function(Blueprint $table){
            // $table->renameColumn('aproved','approved');
            DB::statement('ALTER TABLE composers CHANGE aproved approved datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('composers', function(Blueprint $table){
            // $table->renameColumn('approved','aproved');
            DB::statement('ALTER TABLE composers CHANGE approved aproved datetime');
        });
    }
};