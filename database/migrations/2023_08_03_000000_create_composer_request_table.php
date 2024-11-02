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
        Schema::create('composer_request', function(Blueprint $table){
            $table->id();
            $table->foreignId('composers_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('request_date');
            $table->enum('request_status',['Pendiente','En curso','Terminado']);
            $table->text('description');
            $table->boolean('approved');
            $table->foreignId('approved_by')->nullable();
            $table->datetime('approved_date');
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('composer_request');
    }
};
