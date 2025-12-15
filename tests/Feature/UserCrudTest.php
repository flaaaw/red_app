<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
        ];

        $response = $this->postJson(route('api.user.store'), $userData);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test User', 'email' => 'test@example.com', 'phone' => '1234567890']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'phone' => '1234567890']);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'phone' => '0987654321',
        ];

        $response = $this->putJson(route('api.user.update', $user), $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name', 'phone' => '0987654321']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'phone' => '0987654321']);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson(route('api.user.destroy', $user));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
