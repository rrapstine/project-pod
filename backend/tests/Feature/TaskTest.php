<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// ============================================================================
// LISTING TESTS - Test the index() method
// ============================================================================

it('allows authenticated users to list their tasks', function () {
    // ARRANGE - Create users and tasks
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create 3 tasks for our user
    Task::factory()->count(3)->create(['user_id' => $user->id]);
    // Create 2 tasks for another user (shouldn't see these)
    Task::factory()->count(2)->create(['user_id' => $otherUser->id]);

    // ACT - Make the request
    $response = $this->actingAs($user)->getJson('/v1/tasks');

    // ASSERT - Check we got only OUR tasks
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data'); // Should only see our 3 tasks
});

it('prevents unauthenticated users from listing tasks', function () {
    $response = $this->getJson('/v1/tasks');

    $response->assertStatus(401);
});

it('allows users to list tasks within a specific project', function () {
    // ARRANGE
    $user = User::factory()->create();
    $project1 = Project::factory()->create(['user_id' => $user->id]);
    $project2 = Project::factory()->create(['user_id' => $user->id]);

    // Create 3 tasks in project1
    Task::factory()->count(3)->create([
        'user_id' => $user->id,
        'project_id' => $project1->id,
    ]);

    // Create 2 tasks in project2
    Task::factory()->count(2)->create([
        'user_id' => $user->id,
        'project_id' => $project2->id,
    ]);

    // ACT - Request tasks from project1
    $response = $this->actingAs($user)->getJson("/v1/projects/{$project1->id}/tasks");

    // ASSERT - Should only see project1's 3 tasks
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('prevents users from listing tasks in another users project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/v1/projects/{$otherProject->id}/tasks");

    $response->assertStatus(403); // Forbidden
});

// ============================================================================
// CREATION TESTS - Test the store() method
// ============================================================================

it('allows users to create tasks in their project', function () {
    // ARRANGE
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $taskData = [
        'title' => 'New Task',
        'description' => 'A test task',
        'status' => 'todo',
        'due_date' => date('2025-01-01'),
    ];

    // ACT
    $response = $this->actingAs($user)->postJson("/v1/projects/{$project->id}/tasks", $taskData);

    // ASSERT
    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'title', 'description', 'status', 'due_date', 'project_id', 'project_name'],
            'status',
        ])
        ->assertJson([
            'data' => [
                'title' => 'New Task',
                'description' => 'A test task',
                'status' => 'todo',
            ],
        ]);

    // Verify it's in the database
    $this->assertDatabaseHas('tasks', [
        'title' => 'New Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
});

it('allows users to create task without optional fields', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/v1/projects/{$project->id}/tasks", [
        'title' => 'Minimal Task',
        'status' => 'todo',
        'due_date' => date('2025-01-01'),
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Minimal Task',
        'description' => null,
        'status' => 'todo',
    ]);
});

it('prevents creating task without title', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/v1/projects/{$project->id}/tasks", [
        'description' => 'No title provided',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('prevents users from creating tasks in another users project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->postJson("/v1/projects/{$otherProject->id}/tasks", [
        'title' => 'Sneaky Task',
        'status' => 'todo',
        'due_date' => date('2025-01-01'),
    ]);

    $response->assertStatus(403);
});

it('prevents unauthenticated users from creating tasks', function () {
    $project = Project::factory()->create();

    $response = $this->postJson("/v1/projects/{$project->id}/tasks", [
        'title' => 'Test Task',
        'status' => 'todo',
        'due_date' => date('2025-01-01'),
    ]);

    $response->assertStatus(401);
});

// ============================================================================
// VIEW TESTS - Test the show() method
// ============================================================================

it('allows users to view their task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
    ]);

    $response = $this->actingAs($user)->getJson("/v1/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'project_id' => $project->id,
                'project_name' => $project->name,
            ],
        ]);
});

it('prevents users from viewing another users task', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/v1/tasks/{$otherTask->id}");

    $response->assertStatus(403);
});

it('prevents unauthenticated users from viewing tasks', function () {
    $task = Task::factory()->create();

    $response = $this->getJson("/v1/tasks/{$task->id}");

    $response->assertStatus(401);
});

it('returns 404 when viewing nonexistent task', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/v1/tasks/99999');

    $response->assertStatus(404);
});

// ============================================================================
// UPDATE TESTS - Test the update() method
// ============================================================================

it('allows users to update their task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'title' => 'Old Title',
        'description' => 'Old description',
        'status' => 'todo',
        'due_date' => date('2025-01-01'),
    ]);

    $updateData = [
        'title' => 'Updated Title',
        'description' => 'Updated description',
        'status' => 'in_progress',
        'due_date' => date('2025-01-02'),
    ];

    $response = $this->actingAs($user)->putJson("/v1/tasks/{$task->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'status' => 'in_progress',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title',
        'description' => 'Updated description',
        'status' => 'in_progress',
    ]);
});

it('allows users to partially update task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original',
        'description' => 'Original description',
    ]);

    // Only update the title
    $response = $this->actingAs($user)->putJson("/v1/tasks/{$task->id}", [
        'title' => 'Updated Title Only',
    ]);

    $response->assertStatus(200);

    // Description should remain unchanged
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title Only',
        'description' => 'Original description',
    ]);
});

it('prevents users from updating another users task', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/v1/tasks/{$otherTask->id}", [
        'title' => 'Hacked Title',
    ]);

    $response->assertStatus(403);
});

it('prevents updating task with empty title', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/v1/tasks/{$task->id}", [
        'title' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

it('prevents unauthenticated users from updating tasks', function () {
    $task = Task::factory()->create();

    $response = $this->putJson("/v1/tasks/{$task->id}", [
        'title' => 'New Title',
    ]);

    $response->assertStatus(401);
});

// ============================================================================
// DELETE TESTS - Test the destroy() method
// ============================================================================

it('allows users to delete their task', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/v1/tasks/{$task->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

it('prevents users from deleting another users task', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->deleteJson("/v1/tasks/{$otherTask->id}");

    $response->assertStatus(403);

    // Task should still exist
    $this->assertDatabaseHas('tasks', [
        'id' => $otherTask->id,
    ]);
});

it('prevents unauthenticated users from deleting tasks', function () {
    $task = Task::factory()->create();

    $response = $this->deleteJson("/v1/tasks/{$task->id}");

    $response->assertStatus(401);
});

it('returns 404 when deleting nonexistent task', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/v1/tasks/99999');

    $response->assertStatus(404);
});
