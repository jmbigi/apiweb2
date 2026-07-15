<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Instrument;
use App\Models\StyleMusic;
use Carbon\Carbon;

class ApprovedDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    
    public function run(): void
    {
        $recordsToUpdate = Instrument::whereNull('approved')->get();
        $styleMusicToUpdate = StyleMusic::whereNull('approved')->get();

        // Update the date_column for each record
        foreach ($recordsToUpdate as $record) {
            $record->update([
                'approved' => Carbon::now(), // Set to current datetime
                'request' => Carbon::now(),
            ]);
        }
        foreach ($styleMusicToUpdate as $record) {
            $record->update([
                'approved' => Carbon::now(), // Set to current datetime
                'request' => Carbon::now(),
            ]);
        }
    }
}
