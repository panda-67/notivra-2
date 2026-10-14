<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HireDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_hire_application(): void
    {
        $response = $this->get(route('hire.apply'));

        $response->assertOk();
    }

    public function test_guest_can_create_hire_draft(): void
    {
        $service = Service::factory()->create();

        $response = $this->post(route('hire.draft.store'), [
            'step' => 1,
            'service_id' => $service->id,
            'topic' => 'Website development',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'description' => 'Build a Laravel application.',
        ]);

        $draft = HireDraft::first();

        $this->assertDatabaseCount('hire_drafts', 1);

        $this->assertNotNull($draft);

        $response->assertRedirect(
            route('login', ['d' => $draft->id])
        );

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'user_id' => null,
            'step' => 2,
            'status' => 'draft',
        ]);
    }

    public function test_authenticated_user_can_create_hire_draft(): void
    {
        $user = User::factory()->create();

        $service = Service::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hire.draft.store'), [
            'step' => 1,
            'service_id' => $service->id,
            'topic' => 'Research project',
            'name' => $user->name,
            'email' => $user->email,
            'description' => 'Research assistance.',
        ]);

        $draft = HireDraft::first();

        $this->assertNotNull($draft);

        $response->assertRedirect(
            route('hire.apply', ['d' => $draft->id])
        );

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'user_id' => $user->id,
            'step' => 2,
            'status' => 'draft',
        ]);
    }

    public function test_hire_draft_requires_valid_service(): void
    {
        $response = $this->post(route('hire.draft.store'), [
            'step' => 1,
            'service_id' => '00000000-0000-0000-0000-000000000000',
            'topic' => 'Website development',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertSessionHasErrors(['service_id']);

        $this->assertDatabaseCount('hire_drafts', 0);
    }

    public function test_hire_draft_requires_topic(): void
    {
        $service = Service::factory()->create();

        $response = $this->post(route('hire.draft.store'), [
            'step' => 1,
            'service_id' => $service->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertSessionHasErrors(['topic']);

        $this->assertDatabaseCount('hire_drafts', 0);
    }

    public function test_hire_draft_requires_valid_email(): void
    {
        $service = Service::factory()->create();

        $response = $this->post(route('hire.draft.store'), [
            'step' => 1,
            'service_id' => $service->id,
            'topic' => 'Website development',
            'name' => 'John Doe',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('hire_drafts', 0);
    }

    public function test_authenticated_user_can_update_hire_draft(): void
    {
        $user = User::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 2,
            'data' => [
                'topic' => 'Original topic',
            ],
            'status' => 'draft',
        ]);

        $this->actingAs($user);

        $response = $this->put(
            route('hire.draft.update', ['draft' => $draft]),
            [
                'step' => 2,
                'output' => 'Final report',
                'field' => 'Biology',
                'level' => 'University',
                'institution' => 'USK',
                'next_step' => 3,
            ]
        );

        $response->assertRedirect();

        $draft->refresh();

        $this->assertSame(3, $draft->step);
        $this->assertSame('Final report', $draft->data['output']);
        $this->assertSame('Biology', $draft->data['field']);
    }

    public function test_hire_draft_update_requires_authentication(): void
    {
        $draft = HireDraft::create([
            'user_id' => null,
            'step' => 2,
            'data' => [],
            'status' => 'draft',
        ]);

        $response = $this->put(
            route('hire.draft.update', ['draft' => $draft]),
            [
                'step' => 2,
                'output' => 'Final report',
            ]
        );

        $response->assertRedirect(route('login'));
    }
}
