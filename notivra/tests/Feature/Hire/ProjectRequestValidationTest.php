<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_one_rejects_missing_required_fields(): void
    {
        $service = Service::factory()->create();

        $response = $this->from(route('hire.apply'))
            ->post(route('hire.draft.store'), [
                'step' => 1,
                'service_id' => $service->id,
                'topic' => '',
                'name' => '',
                'email' => '',
            ]);

        $response
            ->assertRedirect(route('hire.apply'))
            ->assertSessionHasErrors([
                'topic',
                'name',
                'email',
            ]);

        $this->assertDatabaseCount('hire_drafts', 0);
    }

    public function test_step_one_rejects_invalid_email(): void
    {
        $service = Service::factory()->create();

        $response = $this->from(route('hire.apply'))
            ->post(route('hire.draft.store'), [
                'step' => 1,
                'service_id' => $service->id,
                'topic' => 'Research topic',
                'name' => 'Test User',
                'email' => 'not-an-email',
            ]);

        $response
            ->assertRedirect(route('hire.apply'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('hire_drafts', 0);
    }

    public function test_step_one_rejects_nonexistent_service(): void
    {
        $response = $this->from(route('hire.apply'))
            ->post(route('hire.draft.store'), [
                'step' => 1,
                'service_id' => '00000000-0000-0000-0000-000000000000',
                'topic' => 'Research topic',
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertRedirect(route('hire.apply'))
            ->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('hire_drafts', 0);
    }

    public function test_step_three_rejects_negative_budget(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 3,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Budget validation test',
                'name' => $user->name,
                'email' => $user->email,
                'output' => 'Research report',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('hire.draft.submit', $draft), [
                'step' => 3,
                'deadline' => now()->addDays(7)->toDateString(),
                'budget' => -1000,
                'willing_dp' => false,
            ])
            ->assertSessionHasErrors('budget');

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('projects', 0);
    }
}
