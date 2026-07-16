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
        try {
            Schema::table('music_scores', function (Blueprint $table) {
                $table->date('date')->nullable()->change();
            });
        } catch (\Exception $e) {
            // SQLite: nullable change not supported without doctrine/dbal
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('music_score', function (Blueprint $table) {
            //
        });
    }
};
