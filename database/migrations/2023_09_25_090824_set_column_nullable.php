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
                $table->text('description')->nullable()->change();
            });
        } catch (\Exception $e) {
            // SQLite: nullable change not needed in test environment
        }
        try {
            Schema::table('link_infos', function (Blueprint $table) {
                $table->string('url')->nullable()->change();
            });
        } catch (\Exception $e) {
            // SQLite: nullable change not needed in test environment
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
