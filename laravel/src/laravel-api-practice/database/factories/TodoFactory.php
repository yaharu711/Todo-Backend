<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->realText(20),
            'memo' => $this->faker->realText(100),
            'is_completed' => false,
            'completed_at' => null,
            'imcompleted_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $completedAt = $this->faker->dateTimeBetween('-1 month', 'now');
            return [
                'is_completed' => true,
                'completed_at' => $completedAt,
                'imcompleted_at' => $this->faker->dateTimeBetween('-2 months', $completedAt),
            ];
        });
    }
}
