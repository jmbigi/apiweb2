<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ComposerStatus extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('composer_status')->truncate();
        DB::table('request_status')->truncate();

        DB::table('composer_status')->insert([
            ['name' => 'Pending'],
            ['name' => 'Active'],
            ['name' => 'Rejected'],
            ['name' => 'Suspended']            
        ]);

        DB::table('request_status')->insert([
            ['name' => 'Pending'],
            ['name' => 'In Progress'],
            ['name' => 'Completed']            
        ]);

    }
}
