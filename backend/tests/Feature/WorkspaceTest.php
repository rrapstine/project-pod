<?php

use App\Models\User;
use App\Models\Workspace;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows authenticated users to list their workspaces', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Workspace::factory()->count(3)->create(['user_id' => $user->id]);
    Workspace::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson('/v1/workspaces');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('prevents unauthenticated users from listing workspaces', function () {
    $response = $this->getJson('/v1/workspaces');

    $response->assertStatus(401);
});

it('allows authenticated users to create workspace with valid data', function () {
    $user = User::factory()->create();

    $workspaceData = [
        'name' => 'My Workspace',
        'description' => 'A workspace for my projects',
        'color' => '#3b82f6',
    ];

    $response = $this->actingAs($user)->postJson('/v1/workspaces', $workspaceData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'name', 'description', 'color', 'created_at', 'updated_at'],
            'status',
        ])
        ->assertJson([
            'data' => [
                'name' => 'My Workspace',
                'description' => 'A workspace for my projects',
                'color' => '#3b82f6',
            ],
        ]);

    $this->assertDatabaseHas('workspaces', [
        'name' => 'My Workspace',
        'user_id' => $user->id,
    ]);
});

it('allows authenticated users to create workspace without optional fields', function () {
    $user = User::factory()->create();

    $workspaceData = [
        'name' => 'Minimal Workspace',
    ];

    $response = $this->actingAs($user)->postJson('/v1/workspaces', $workspaceData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('workspaces', [
        'name' => 'Minimal Workspace',
        'user_id' => $user->id,
        'description' => null,
        'color' => null,
    ]);
});

it('prevents creating workspace without name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/v1/workspaces', [
        'description' => 'A workspace',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('prevents creating workspace with invalid color', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/v1/workspaces', [
        'name' => 'Test Workspace',
        'color' => 'not-a-hex-color',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);
});

it('prevents unauthenticated users from creating workspaces', function () {
    $response = $this->postJson('/v1/workspaces', [
        'name' => 'Test Workspace',
    ]);

    $response->assertStatus(401);
});

it('allows authenticated users to view their workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson("/v1/workspaces/{$workspace->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'description', 'color', 'created_at', 'updated_at'],
        ])
        ->assertJson([
            'data' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
            ],
        ]);
});

it('prevents users from viewing another users workspace', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/v1/workspaces/{$workspace->id}");

    $response->assertStatus(403);
});

it('prevents unauthenticated users from viewing workspaces', function () {
    $workspace = Workspace::factory()->create();

    $response = $this->getJson("/v1/workspaces/{$workspace->id}");

    $response->assertStatus(401);
});

it('returns 404 when viewing nonexistent workspace', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/v1/workspaces/99999');

    $response->assertStatus(404);
});

it('allows authenticated users to update their workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'description' => 'Old description',
    ]);

    $updateData = [
        'name' => 'New Name',
        'description' => 'New description',
        'color' => '#ef4444',
    ];

    $response = $this->actingAs($user)->putJson("/v1/workspaces/{$workspace->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $workspace->id,
                'name' => 'New Name',
                'description' => 'New description',
                'color' => '#ef4444',
            ],
        ]);

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'New Name',
        'description' => 'New description',
        'color' => '#ef4444',
    ]);
});

it('allows authenticated users to partially update workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create([
        'user_id' => $user->id,
        'name' => 'Original Name',
        'description' => 'Original description',
        'color' => '#3b82f6',
    ]);

    $response = $this->actingAs($user)->putJson("/v1/workspaces/{$workspace->id}", [
        'name' => 'Updated Name Only',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'Updated Name Only',
        'description' => 'Original description',
        'color' => '#3b82f6',
    ]);
});

it('prevents users from updating another users workspace', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/v1/workspaces/{$workspace->id}", [
        'name' => 'Hacked Name',
    ]);

    $response->assertStatus(403);
});

it('prevents updating workspace with empty name', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/v1/workspaces/{$workspace->id}", [
        'name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('prevents unauthenticated users from updating workspaces', function () {
    $workspace = Workspace::factory()->create();

    $response = $this->putJson("/v1/workspaces/{$workspace->id}", [
        'name' => 'New Name',
    ]);

    $response->assertStatus(401);
});

it('allows authenticated users to delete their workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/v1/workspaces/{$workspace->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('workspaces', [
        'id' => $workspace->id,
    ]);
});

it('prevents users from deleting another users workspace', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->deleteJson("/v1/workspaces/{$workspace->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
    ]);
});

it('prevents unauthenticated users from deleting workspaces', function () {
    $workspace = Workspace::factory()->create();

    $response = $this->deleteJson("/v1/workspaces/{$workspace->id}");

    $response->assertStatus(401);
});

it('returns 404 when deleting nonexistent workspace', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/v1/workspaces/99999');

    $response->assertStatus(404);
});
