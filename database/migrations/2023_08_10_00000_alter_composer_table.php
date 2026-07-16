<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Facade;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('composer_status', function(Blueprint $table){
            $table->id();
            $table->string('name');                        
            $table->timestamps();
        });
        
        /* Insertar datos en la tabla composer_status */
        DB::table('composer_status')->insert([
            ['name' => 'Pendiente'],
            ['name' => 'Activo'],
            ['name' => 'Rechazado'],
            ['name' => 'Suspendido']            
        ]);

        
        Schema::create('request_status', function(Blueprint $table){
            $table->id();
            $table->string('name');                        
            $table->timestamps();
        });
        /* Insertar datos en la tabla composer_status */
        DB::table('request_status')->insert([
            ['name' => 'Pendiente'],
            ['name' => 'En curso'],
            ['name' => 'Terminado']
        ]);

        try {
            Schema::table('composers', function(Blueprint $table){
                $table->dropColumn('approved');
                $table->dropColumn('request');
            });
        } catch (\Exception $e) {
            // SQLite < 3.35 does not support DROP COLUMN — skip gracefully
        }
        
        try {
            Schema::table('composer_request', function(Blueprint $table)
            {
                $table->dropColumn('request_status');
                $table->dropColumn('approved');
                $table->dropColumn('approved_date');
            });
        } catch (\Exception $e) {
            // SQLite < 3.35 does not support DROP COLUMN — skip gracefully
        }

        try {
            Schema::table('composer_request', function(Blueprint $table) {
                $table->renameColumn('approved_by', 'updated_by');
            });
        } catch (\Exception $e) {
            // Column might already be renamed
        }

        Schema::table('composer_request', function(Blueprint $table) {
            $table->unsignedBigInteger('composer_status_id')->nullable();
            $table->unsignedBigInteger('request_status_id')->nullable();            
            $table->foreign('composer_status_id')->references('id')->on('composer_status')->onDelete('set null');
            $table->foreign('request_status_id')->references('id')->on('request_status')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('composer_status');
        Schema::dropIfExists('request_status');
        try {
            Schema::table('composer_request', function(Blueprint $table)
            {
                $table->dropForeign(['composer_status_id']);
                $table->dropForeign(['request_status_id']);
                $table->dropColumn('composer_status_id');
                $table->dropColumn('request_status_id');
                $table->renameColumn('updated_by', 'approved_by');
            });
        } catch (\Exception $e) {
            // SQLite compatibility: skip unsupported operations
        }
        try {
            Schema::table('composers', function(Blueprint $table){
                $table->string('approved');
                $table->string('request');
            });
        } catch (\Exception $e) {
            // SQLite compatibility: columns might already exist
        }
    }
};
