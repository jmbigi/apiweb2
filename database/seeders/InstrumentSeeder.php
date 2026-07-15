<?php

namespace Database\Seeders;

use App\Models\FamilyInstruments;
use App\Models\Instrument;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentSeeder extends Seeder
{

    protected $data = [
        [
            'family' => 'Wood winds',
            'instruments' => [
                ['name' => 'Oboe'],
                ['name' => 'Piccolo'],
                ['name' => 'Flute'],
                ['name' => 'Clarinet'],
                ['name' => 'Bass clarinet'],
                ['name' => 'Soprano Saxophone'],
                ['name' => 'Alto Saxophone'],
                ['name' => 'Tenor Saxophone'],
                ['name' => 'Baritone Saxophone'],
                ['name' => 'Basson'],
            ]
        ],
        [
            'family' => 'Brass Wind',
            'instruments' => [
                ['name' => 'Trumpet'],
                ['name' => 'French horn'],
                ['name' => 'Euphonium'],
                ['name' => 'Trombone'],
                ['name' => 'Tuba']
            ]
        ],
        [
            'family' => 'Strigs',
            'instruments' => [
                ['name' => 'Guitar'],
                ['name' => 'Violin'],
                ['name' => 'Viola'],
                ['name' => 'Cello'],
                ['name' => 'Contrabass'],
                ['name' => 'Piano'],
                ['name' => 'Harp'],
                ['name' => 'new']
            ]
        ],
        [
            'family' => 'Percussion',
            'instruments' => [
                ['name' => 'Snare'],
                ['name' => 'Timpani'],
                ['name' => 'Marimba'],
                ['name' => 'Vibraphone'],
                ['name' => 'Drum set'],
                ['name' => 'Multi percussion / Set-Up']
            ]
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instruments_data = collect($this->data);
        DB::beginTransaction();
        try{
            $instruments_data->each(function ($data_row){
                $family = FamilyInstruments::where('name',$data_row['family'])->firstOrFail();
                $collect_instruments = collect($data_row['instruments']);
                $collect_instruments = $collect_instruments->map(function ($instrument) use ($family){
                    return [
                        'name' => $instrument['name'],
                        'family_instruments_id' => $family->id
                    ];
                });
                //añadir 'family_instruments_id' => $family->id en cada array nested y se hace el upsert
                Instrument::upsert($collect_instruments->toArray(),['name']);
            });
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            throw new Exception($e->getMessage(). ' -- Trace:'.$e->getTrace());
        }
        
    }
}
