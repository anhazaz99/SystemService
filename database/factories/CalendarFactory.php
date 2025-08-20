<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\app\Models\Calendar;

class CalendarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Calendar::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+1 month');
        $endTime = $this->faker->dateTimeBetween($startTime, '+2 months');
        
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'event_type' => $this->faker->randomElement(['event', 'task']),
            'participant_id' => $this->faker->numberBetween(1, 10),
            'participant_type' => $this->faker->randomElement(['lecturer', 'student']),
            'creator_id' => $this->faker->numberBetween(1, 10),
            'creator_type' => $this->faker->randomElement(['lecturer', 'student']),
        ];
    }
}
