<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = \App\Models\User::factory()->create();
        $workspace = \App\Models\Workspace::factory()->create(['user_id' => $user->id]);

        return [
            'user_id' => $user->id,
            'workspace_id' => $workspace->id,
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'archived' => false,
        ];
    }
}
