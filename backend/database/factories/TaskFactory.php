<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = \App\Models\User::factory()->create();
        $project = \App\Models\Project::factory()->create(['user_id' => $user->id]);

        return [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
