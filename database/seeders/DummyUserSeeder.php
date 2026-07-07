<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

use App\Models\MusicScore;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // $numberOfRecords = 10; 
        // for ($i = 0; $i < $numberOfRecords; $i++) {
        //     DB::table('composers')->insert([    
        //         'public_name' => 'composer',  
        //         'users_id' => rand(1, 10), 
        //         'created_at' => now(),  
        //         'updated_at' => now(),  
        //     ]);
        // }
        
        // $numberOfRecords = 4; 
        
        // for ($i = 0; $i < $numberOfRecords; $i++) {
        //     DB::table('composer_request')->insert([    
        //         'composers_id' => rand(1, 10),  
        //         'request_date' => now(), 
        //         'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        //         'updated_by' => 1,
        //         'created_at' => now(),  
        //         'updated_at' => now(),  
        //         'composer_status_id' => rand(1, 4),  
        //         'request_status_id' => rand(1, 3), 
        //     ]);
        // }
        // $numberOfRecords = 10; 
        
        // for ($i = 0; $i < $numberOfRecords; $i++) {
        //     DB::table('music_scores')->insert([    
        //         'name' => $faker->name,  
        //         'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        //         'owner_id' => rand(2, 20),
        //         'created_at' => now(),  
        //         'updated_at' => now(),  
        //     ]);
        // }
        MusicScore::factory()->count(20)->create();
    }
}
