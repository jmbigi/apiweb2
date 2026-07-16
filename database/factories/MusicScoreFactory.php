<?php

namespace Database\Factories;

use App\Models\MusicScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class MusicScoreFactory extends Factory
{
    protected $model = MusicScore::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(), // Use text() to generate a random text
            'owner_id' => $this->faker->numberBetween(2, 20), // Use numberBetween() to generate a random number
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
