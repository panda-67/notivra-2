<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HireDraftAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_with_existing_draft_can_login_and_claim_draft(): void
    {
        $draft = HireDraft::create([
            'user_id' => null,
            'step' => 2,
            'data' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'topic' => 'Research project',
            ],
            'status' => 'draft',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'john@example.com',
            'password' => 'password123',
            'draft' => $draft->id,
        ]);

        $response->assertRedirect(
            route('hire.apply', ['d' => $draft->id])
        );

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);

        $this->assertAuthenticatedAs($user);

        $draft->refresh();

        $this->assertSame($user->id, $draft->user_id);

        $this->assertTrue(
            Hash::check('password123', $user->password)
        );
    }

    public function test_existing_user_can_login_with_hire_draft(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $draft = HireDraft::create([
            'user_id' => null,
            'step' => 2,
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'status' => 'draft',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
            'draft' => $draft->id,
        ]);

        $response->assertRedirect(
            route('hire.apply', ['d' => $draft->id])
        );

        $this->assertAuthenticatedAs($user);

        $draft->refresh();

        $this->assertSame($user->id, $draft->user_id);
    }

    public function test_invalid_draft_id_does_not_allow_automatic_registration(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'john@example.com',
            'password' => 'password123',
            'draft' => '00000000-0000-0000-0000-000000000000',
        ]);

        $response->assertSessionHasErrors();

        $this->assertGuest();

        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);
    }
}
