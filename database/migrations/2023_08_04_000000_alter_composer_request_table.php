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
        Schema::table('composer_request', function(Blueprint $table){
            $table->timestamp('request_date')->default(DB::raw('CURRENT_TIMESTAMP'))->change();
            $table->enum('request_status',['Pendiente','En curso','Terminado'])->default('Pendiente')->change();
            $table->text('description')->nullable()->change();
            $table->boolean('approved')->nullable()->change();
            $table->datetime('approved_date')->nullable()->change();
        });   
    }
};