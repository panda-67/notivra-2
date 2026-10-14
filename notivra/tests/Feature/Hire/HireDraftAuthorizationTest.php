<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HireDraftAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_update_another_users_draft(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $owner->id,
            'step' => 2,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Original topic',
                'name' => $owner->name,
                'email' => $owner->email,
            ],
        ]);

        $this->actingAs($otherUser)
            ->put(route('hire.draft.update', $draft), [
                'step' => 2,
                'output' => 'Research report',
                'next_step' => 3,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'user_id' => $owner->id,
            'status' => 'draft',
            'step' => 2,
        ]);

        $this->assertSame(
            'Original topic',
            $draft->fresh()->data['topic']
        );
    }

    public function test_user_cannot_submit_another_users_draft(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $owner->id,
            'step' => 3,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Original topic',
                'name' => $owner->name,
                'email' => $owner->email,
                'output' => 'Research report',
            ],
        ]);

        $this->actingAs($otherUser)
            ->post(route('hire.draft.submit', $draft), [
                'step' => 3,
                'deadline' => now()->addDays(7)->toDateString(),
                'budget' => 1000000,
                'willing_dp' => false,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'user_id' => $owner->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('projects', 0);
    }
}
