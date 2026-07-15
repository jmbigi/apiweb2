<?php

namespace Database\Seeders;

use App\Models\StyleMusic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StyleMusicSeeder extends Seeder
{
    protected $data = [
        ['name' => 'Baroque'],
        ['name' => 'Classicism'],
        ['name' => 'Romanticism'],
        ['name' => 'Postromanticism'],
        ['name' => 'Neoclassicism'],
        ['name' => 'Impressionism'],
        ['name' => 'Nationalism'],
        ['name' => 'Dodecaphonism'],
        ['name' => 'Atonalism'],
        ['name' => 'Serialism'],
        ['name' => 'Minimalism'],
        ['name' => 'Jazz'],
        ['name' => 'Soul'],
        ['name' => 'Blues'],
        ['name' => 'Party Music'], //Fiestera
        ['name' => 'Street Music'], //Xaranga
        ['name' => 'Rock'],
        ['name' => 'Reggea'],
        ['name' => 'Contry'],
        ['name' => 'Folk'],
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        StyleMusic::upsert($this->data,['name']);
    }
}
