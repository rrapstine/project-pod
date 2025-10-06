<?php

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// ============================================================================
// LISTING TESTS - Test the index() method
// ============================================================================

it('allows authenticated users to list their projects', function () {
    // ARRANGE - Create users and projects
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create 3 projects for our user
    Project::factory()->count(3)->create(['user_id' => $user->id]);
    // Create 2 projects for another user (shouldn't see these)
    Project::factory()->count(2)->create(['user_id' => $otherUser->id]);

    // ACT - Make the request
    $response = $this->actingAs($user)->getJson('/v1/projects');

    // ASSERT - Check we got only OUR projects
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data'); // Should only see our 3 projects
});

it('prevents unauthenticated users from listing projects', function () {
    $response = $this->getJson('/v1/projects');

    $response->assertStatus(401);
});

it('allows users to list projects within a specific workspace', function () {
    // ARRANGE
    $user = User::factory()->create();
    $workspace1 = Workspace::factory()->create(['user_id' => $user->id]);
    $workspace2 = Workspace::factory()->create(['user_id' => $user->id]);

    // Create 3 projects in workspace1
    Project::factory()->count(3)->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace1->id,
    ]);

    // Create 2 projects in workspace2
    Project::factory()->count(2)->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace2->id,
    ]);

    // ACT - Request projects from workspace1
    $response = $this->actingAs($user)->getJson("/v1/workspaces/{$workspace1->id}/projects");

    // ASSERT - Should only see workspace1's 3 projects
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('prevents users from listing projects in another users workspace', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherWorkspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/v1/workspaces/{$otherWorkspace->id}/projects");

    $response->assertStatus(403); // Forbidden
});

// ============================================================================
// CREATION TESTS - Test the store() method
// ============================================================================

it('allows users to create project in their workspace', function () {
    // ARRANGE
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $projectData = [
        'name' => 'New Project',
        'description' => 'A test project',
    ];

    // ACT
    $response = $this->actingAs($user)->postJson("/v1/workspaces/{$workspace->id}/projects", $projectData);

    // ASSERT
    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name', 'description', 'archived', 'workspace_id', 'workspace_name'],
            'status',
        ])
        ->assertJson([
            'data' => [
                'name' => 'New Project',
                'description' => 'A test project',
                'archived' => false,
            ],
        ]);

    // Verify it's in the database
    $this->assertDatabaseHas('projects', [
        'name' => 'New Project',
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);
});

it('allows users to create project without optional fields', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/v1/workspaces/{$workspace->id}/projects", [
        'name' => 'Minimal Project',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('projects', [
        'name' => 'Minimal Project',
        'description' => null,
        'archived' => false,
    ]);
});

it('prevents creating project without name', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/v1/workspaces/{$workspace->id}/projects", [
        'description' => 'No name provided',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('prevents users from creating project in another users workspace', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherWorkspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->postJson("/v1/workspaces/{$otherWorkspace->id}/projects", [
        'name' => 'Sneaky Project',
    ]);

    $response->assertStatus(403);
});

it('prevents unauthenticated users from creating projects', function () {
    $workspace = Workspace::factory()->create();

    $response = $this->postJson("/v1/workspaces/{$workspace->id}/projects", [
        'name' => 'Test Project',
    ]);

    $response->assertStatus(401);
});

// ============================================================================
// VIEW TESTS - Test the show() method
// ============================================================================

it('allows users to view their project', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $response = $this->actingAs($user)->getJson("/v1/projects/{$project->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'workspace_name' => $workspace->name,
            ],
        ]);
});

it('prevents users from viewing another users project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/v1/projects/{$otherProject->id}");

    $response->assertStatus(403);
});

it('prevents unauthenticated users from viewing projects', function () {
    $project = Project::factory()->create();

    $response = $this->getJson("/v1/projects/{$project->id}");

    $response->assertStatus(401);
});

it('returns 404 when viewing nonexistent project', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/v1/projects/99999');

    $response->assertStatus(404);
});

// ============================================================================
// UPDATE TESTS - Test the update() method
// ============================================================================

it('allows users to update their project', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
        'name' => 'Old Name',
        'archived' => false,
    ]);

    $updateData = [
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'archived' => true,
    ];

    $response = $this->actingAs($user)->putJson("/v1/projects/{$project->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $project->id,
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'archived' => true,
            ],
        ]);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Name',
        'archived' => true,
    ]);
});

it('allows users to partially update project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id,
        'name' => 'Original',
        'description' => 'Original description',
    ]);

    // Only update the name
    $response = $this->actingAs($user)->putJson("/v1/projects/{$project->id}", [
        'name' => 'Updated Name Only',
    ]);

    $response->assertStatus(200);

    // Description should remain unchanged
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Name Only',
        'description' => 'Original description',
    ]);
});

it('prevents users from updating another users project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/v1/projects/{$otherProject->id}", [
        'name' => 'Hacked Name',
    ]);

    $response->assertStatus(403);
});

it('prevents updating project with empty name', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/v1/projects/{$project->id}", [
        'name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('prevents unauthenticated users from updating projects', function () {
    $project = Project::factory()->create();

    $response = $this->putJson("/v1/projects/{$project->id}", [
        'name' => 'New Name',
    ]);

    $response->assertStatus(401);
});

// ============================================================================
// DELETE TESTS - Test the destroy() method
// ============================================================================

it('allows users to delete their project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/v1/projects/{$project->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

it('prevents users from deleting another users project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->deleteJson("/v1/projects/{$otherProject->id}");

    $response->assertStatus(403);

    // Project should still exist
    $this->assertDatabaseHas('projects', [
        'id' => $otherProject->id,
    ]);
});

it('prevents unauthenticated users from deleting projects', function () {
    $project = Project::factory()->create();

    $response = $this->deleteJson("/v1/projects/{$project->id}");

    $response->assertStatus(401);
});

it('returns 404 when deleting nonexistent project', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/v1/projects/99999');

    $response->assertStatus(404);
});
