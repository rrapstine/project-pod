<?php

use App\Models\User;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// ============================================================================
// PROFILE VIEW TESTS - Test the show() method
// ============================================================================

it('allows authenticated users to view their profile', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response = $this->actingAs($user)->getJson('/v1/user');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ]);
});

it('prevents unauthenticated users from viewing profile', function () {
    $response = $this->getJson('/v1/user');

    $response->assertStatus(401);
});

// ============================================================================
// PROFILE UPDATE TESTS - Test the update() method
// ============================================================================

it('allows users to update their name', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'user@example.com',
    ]);

    $response = $this->actingAs($user)->putJson('/v1/user', [
        'name' => 'New Name',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'New Name',
                'email' => 'user@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
    ]);
});

it('prevents updating with empty name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/v1/user', [
        'name' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('allows updating with no changes', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
    ]);

    $response = $this->actingAs($user)->putJson('/v1/user', []);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Original Name',
    ]);
});

it('prevents unauthenticated users from updating profile', function () {
    $response = $this->putJson('/v1/user', [
        'name' => 'New Name',
    ]);

    $response->assertStatus(401);
});

// ============================================================================
// ACCOUNT DELETE TESTS - Test the destroy() method
// ============================================================================

it('allows users to delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession([])
        ->deleteJson('/v1/user');

    $response->assertStatus(204);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('prevents unauthenticated users from deleting accounts', function () {
    $response = $this->deleteJson('/v1/user');

    $response->assertStatus(401);
});
