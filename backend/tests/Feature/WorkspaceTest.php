<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_their_workspaces(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Workspace::factory()->count(3)->create(['user_id' => $user->id]);
        Workspace::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->getJson('/v1/workspaces');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_user_cannot_list_workspaces(): void
    {
        $response = $this->getJson('/v1/workspaces');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_workspace_with_valid_data(): void
    {
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
    }

    public function test_authenticated_user_can_create_workspace_without_optional_fields(): void
    {
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
    }

    public function test_user_cannot_create_workspace_without_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/v1/workspaces', [
            'description' => 'A workspace',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_cannot_create_workspace_with_invalid_color(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/v1/workspaces', [
            'name' => 'Test Workspace',
            'color' => 'not-a-hex-color',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['color']);
    }

    public function test_unauthenticated_user_cannot_create_workspace(): void
    {
        $response = $this->postJson('/v1/workspaces', [
            'name' => 'Test Workspace',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_their_workspace(): void
    {
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
    }

    public function test_user_cannot_view_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->getJson("/v1/workspaces/{$workspace->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->getJson("/v1/workspaces/{$workspace->id}");

        $response->assertStatus(401);
    }

    public function test_viewing_nonexistent_workspace_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/v1/workspaces/99999');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_update_their_workspace(): void
    {
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
    }

    public function test_authenticated_user_can_partially_update_workspace(): void
    {
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
    }

    public function test_user_cannot_update_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->putJson("/v1/workspaces/{$workspace->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_workspace_with_empty_name(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/v1/workspaces/{$workspace->id}", [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_unauthenticated_user_cannot_update_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->putJson("/v1/workspaces/{$workspace->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_their_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/v1/workspaces/{$workspace->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('workspaces', [
            'id' => $workspace->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->deleteJson("/v1/workspaces/{$workspace->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->deleteJson("/v1/workspaces/{$workspace->id}");

        $response->assertStatus(401);
    }

    public function test_deleting_nonexistent_workspace_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/v1/workspaces/99999');

        $response->assertStatus(404);
    }
}
