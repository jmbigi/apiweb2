<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ensemble_user')
            ->where('role', 'usuario')
            ->update(['role' => 'member']);

        DB::table('ensemble_user')
            ->where('role', 'archivero')
            ->update(['role' => 'archivist']);

        DB::table('ensemble_user')
            ->where('role', 'administrador')
            ->update(['role' => 'admin']);

        DB::table('ensemble_user')
            ->where('role', 'maestro')
            ->update(['role' => 'teacher']);

        DB::table('ensemble_user')
            ->where('role', 'director')
            ->update(['role' => 'admin']);

        Schema::table('ensemble_user', function (Blueprint $table) {
            $table->string('role')->default('member')->change();
        });
    }

    public function down(): void
    {
        DB::table('ensemble_user')
            ->where('role', 'member')
            ->update(['role' => 'usuario']);

        DB::table('ensemble_user')
            ->where('role', 'archivist')
            ->update(['role' => 'archivero']);

        DB::table('ensemble_user')
            ->where('role', 'admin')
            ->update(['role' => 'administrador']);

        DB::table('ensemble_user')
            ->where('role', 'teacher')
            ->update(['role' => 'maestro']);
    }
};
