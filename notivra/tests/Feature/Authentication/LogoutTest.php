<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('dashboard'));

        $this->assertGuest();
    }

    public function test_guest_cannot_logout_as_authenticated_user(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect();

        $this->assertGuest();
    }
}
