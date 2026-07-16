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
        Schema::table('subscription_plan', function (Blueprint $table) {
            // Remove the 'interval' column
            $table->dropColumn('interval');

            // Add 'start_date' and 'end_date' columns
            $table->date('start_date')->default(now());
            $table->date('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plan', function (Blueprint $table) {
            // Revert the changes if necessary
            $table->string('interval');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
};
