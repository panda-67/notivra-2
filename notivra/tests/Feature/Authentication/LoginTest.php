<?php

namespace Tests\Feature\Authentication;

use App\Models\HireDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_login_rejects_unknown_email(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'unknown@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_requires_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_login_requires_valid_email(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('dashboard'));

        $this->assertGuest();
    }
}
