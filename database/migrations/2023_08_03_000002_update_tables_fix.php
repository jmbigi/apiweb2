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
        Schema::table('style_musics', function(Blueprint $table){
            // $table->renameColumn('aproved','approved');
            DB::statement('ALTER TABLE style_musics CHANGE aproved approved datetime');
        });

        Schema::table('instruments', function(Blueprint $table){
            // $table->renameColumn('aproved','approved');
            DB::statement('ALTER TABLE instruments CHANGE aproved approved datetime');
        });

        Schema::table('family_instruments', function(Blueprint $table){
            // $table->renameColumn('aproved','approved');
            DB::statement('ALTER TABLE family_instruments CHANGE aproved approved datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('style_musics', function(Blueprint $table){
            // $table->renameColumn('approved','aproved');
            DB::statement('ALTER TABLE style_musics CHANGE approved aproved datetime');
        });
    
        Schema::table('instruments', function(Blueprint $table){
            // $table->renameColumn('approved','aproved');
            DB::statement('ALTER TABLE instruments CHANGE approved aproved datetime');
        });
   
        Schema::table('family_instruments', function(Blueprint $table){
            // $table->renameColumn('approved','aproved');
            DB::statement('ALTER TABLE family_instruments CHANGE approved aproved datetime');
        });
    }
};