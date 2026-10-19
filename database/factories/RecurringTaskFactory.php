<?php

namespace Database\Factories;

use App\Enums\TaskFrequency;
use App\Models\RecurringTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTask>
 */
class RecurringTaskFactory extends Factory
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
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'frequency' => TaskFrequency::Daily,
            'frequency_config' => null,
        ];
    }

    /**
     * Indicate that the recurring task repeats on the given days of the week.
     *
     * @param  list<string>  $days
     */
    public function weekly(array $days = ['monday']): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => TaskFrequency::Weekly,
            'frequency_config' => ['days' => $days],
        ]);
    }

    /**
     * Indicate that the recurring task repeats on the given day of the month.
     */
    public function monthly(int $day = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => TaskFrequency::Monthly,
            'frequency_config' => ['day' => $day],
        ]);
    }
}
