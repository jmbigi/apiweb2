<?php

namespace Database\Factories;

use App\Models\Ensemble;
use App\Models\Rehearsal;
use Illuminate\Database\Eloquent\Factories\Factory;

class RehearsalFactory extends Factory
{
    protected $model = Rehearsal::class;

    public function definition(): array
    {
        return [
            'ensemble_id' => Ensemble::factory(),
            'title' => $this->faker->sentence(3),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'location' => $this->faker->address(),
            'notes' => $this->faker->paragraph(),
        ];
    }
}
