<?php

namespace Database\Seeders;

use App\Models\FamilyInstruments;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FamilyInstrumetSeeder extends Seeder
{
    protected $data = [
        ['name' => 'Wood winds'],
        ['name' => 'Brass Wind'],
        ['name' => 'Strigs'],
        ['name' => 'Percussion'],
        ['name' => 'Not categorized']
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        FamilyInstruments::upsert($this->data,['name']);
    }
}
