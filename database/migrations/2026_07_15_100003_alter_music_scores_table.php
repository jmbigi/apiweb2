<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_scores', function (Blueprint $table) {
            $table->foreignId('ensemble_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->foreignId('ensemble_folder_id')->nullable()->constrained('ensemble_folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('music_scores', function (Blueprint $table) {
            $table->dropForeign(['ensemble_folder_id']);
            $table->dropForeign(['uploaded_by']);
            $table->dropForeign(['ensemble_id']);
            $table->dropColumn(['ensemble_id', 'uploaded_by', 'ensemble_folder_id']);
        });
    }
};
