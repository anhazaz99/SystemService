<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFileFactoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Task\Models\TaskFileFactory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

