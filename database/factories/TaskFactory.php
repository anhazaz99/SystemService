<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\app\Models\Task;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'receiver_id' => $this->faker->numberBetween(1, 10),
            'receiver_type' => $this->faker->randomElement(['lecturer', 'student']),
            'creator_id' => $this->faker->numberBetween(1, 10),
            'creator_type' => $this->faker->randomElement(['lecturer', 'student']),
        ];
    }
}
