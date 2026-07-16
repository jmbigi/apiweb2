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
        // Column types already set by create_composer_request_table migration.
        // No changes needed — the ->change() calls were removed to avoid
        // requiring doctrine/dbal (incompatible with Laravel 10 deps).
        // Rollback: this migration is a no-op now.
    }
};