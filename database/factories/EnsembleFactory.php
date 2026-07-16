<?php

namespace Database\Factories;

use App\Models\Ensemble;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnsembleFactory extends Factory
{
    protected $model = Ensemble::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'cif' => $this->faker->unique()->bothify('?######'),
            'description' => $this->faker->sentence(),
            'owner_id' => \App\Models\User::factory(),
        ];
    }
}
