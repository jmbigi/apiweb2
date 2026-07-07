<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Laratrust\Laratrust;

class DatabaseSeeder extends Seeder
{

    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            StyleMusicSeeder::class,
            FamilyInstrumetSeeder::class,
            InstrumentSeeder::class,
            LaratrustSeeder::class,
      //      DefaultLaratrustSeeder::class,
            ComposerStatus::class,
            AdminPermissonSeeder::class,
            DummyUserSeeder::class,
            ApprovedDateSeeder::class,
            TestUserSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
