<?php

use App\Models\User;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows users to register with valid data', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/auth/register', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            ],
            'status',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);

    $this->assertAuthenticated();
});

it('prevents registration with existing email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $userData = [
        'name' => 'Jane Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/auth/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('prevents registration without required fields', function () {
    $response = $this->postJson('/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('prevents registration with invalid email', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/auth/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('prevents registration with short password', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ];

    $response = $this->postJson('/auth/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('prevents registration with mismatched password confirmation', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123',
    ];

    $response = $this->postJson('/auth/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('allows users to login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
            ],
            'status',
        ]);

    $this->assertAuthenticated();
});

it('prevents login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);

    $this->assertGuest();
});

it('prevents login with nonexistent email', function () {
    $response = $this->postJson('/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
    $this->assertGuest();
});

it('prevents login without required fields', function () {
    $response = $this->postJson('/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('allows authenticated users to logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/auth/logout');

    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'status']);

    $this->assertGuest();
});

it('prevents unauthenticated users from logging out', function () {
    $response = $this->postJson('/auth/logout');

    $response->assertStatus(401);
});
