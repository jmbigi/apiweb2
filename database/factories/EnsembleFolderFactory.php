<?php

namespace Database\Factories;

use App\Models\Ensemble;
use App\Models\EnsembleFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnsembleFolderFactory extends Factory
{
    protected $model = EnsembleFolder::class;

    public function definition(): array
    {
        return [
            'ensemble_id' => Ensemble::factory(),
            'name' => $this->faker->word(),
            'path' => '/',
        ];
    }
}
