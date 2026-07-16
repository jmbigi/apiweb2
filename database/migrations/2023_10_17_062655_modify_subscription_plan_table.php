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
            Schema::table('subscription_plan', function (Blueprint $table) {
                $table->dropColumn('interval');
                $table->date('start_date')->default(now());
                $table->date('end_date')->nullable();
            });
        } catch (\Exception $e) {
            // SQLite < 3.35 does not support DROP COLUMN
            // Still try adding columns
            try {
                Schema::table('subscription_plan', function (Blueprint $table) {
                    $table->date('start_date')->default(now());
                    $table->date('end_date')->nullable();
                });
            } catch (\Exception $e2) {
                // Columns might already exist
            }
        }
    }

    public function down(): void
    {
        try {
            Schema::table('subscription_plan', function (Blueprint $table) {
                $table->string('interval');
                $table->dropColumn('start_date');
                $table->dropColumn('end_date');
            });
        } catch (\Exception $e) {
            // SQLite < 3.35 does not support DROP COLUMN
        }
    }
};
