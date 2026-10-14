<?php

namespace Tests\Feature\Hire;

use App\Mail\ProjectSubmittedMail;
use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HireDraftSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_a_completed_draft(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 3,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Research topic',
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '08123456789',
                'output' => 'Research report',
            ],
        ]);

        $response = $this->actingAs($user)->post(
            route('hire.draft.submit', $draft),
            [
                'step' => 3,
                'deadline' => now()->addDays(7)->toDateString(),
                'budget' => 1000000,
                'willing_dp' => false,
            ]
        );

        $response
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'submitted',
        ]);

        $this->assertDatabaseCount('projects', 1);

        $this->assertDatabaseHas('projects', [
            'service_id' => $service->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'topic' => 'Research topic',
            'status' => 'pending',
        ]);

        Mail::assertQueued(
            ProjectSubmittedMail::class,
            fn(ProjectSubmittedMail $mail) => $mail->hasTo($user->email)
        );

        Mail::assertQueuedCount(1);
    }

    public function test_submitting_the_same_draft_twice_does_not_create_duplicate_projects(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 3,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Duplicate submission test',
                'name' => $user->name,
                'email' => $user->email,
                'output' => 'Research report',
            ],
        ]);

        $payload = [
            'step' => 3,
            'deadline' => now()->addDays(7)->toDateString(),
            'budget' => 1000000,
            'willing_dp' => false,
        ];

        $this->actingAs($user)
            ->post(route('hire.draft.submit', $draft), $payload)
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->post(
            route('hire.draft.submit', $draft),
            $payload
        )
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('info');

        $this->assertDatabaseCount('projects', 1);

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'submitted',
        ]);

        Mail::assertQueuedCount(1);
    }

    public function test_draft_cannot_be_submitted_before_step_three(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 2,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Incomplete draft',
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        $this->actingAs($user)
            ->from(route('hire.apply', ['d' => $draft->id]))
            ->post(route('hire.draft.submit', $draft), [
                'step' => 3,
                'deadline' => now()->addDays(7)->toDateString(),
            ])
            ->assertRedirect(route('hire.apply', ['d' => $draft->id]))
            ->assertSessionHasErrors('message');

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('projects', 0);

        Mail::assertNothingSent();
    }

    public function test_submission_rejects_a_past_deadline(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 3,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Deadline validation test',
                'name' => $user->name,
                'email' => $user->email,
                'output' => 'Research report',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('hire.draft.submit', $draft), [
                'step' => 3,
                'deadline' => now()->subDay()->toDateString(),
                'budget' => 1000000,
                'willing_dp' => false,
            ])
            ->assertSessionHasErrors('deadline');

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseCount('projects', 0);

        Mail::assertNothingSent();
    }

    public function test_project_creation_failure_rolls_back_draft_submission(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 3,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Transaction rollback test',
                'name' => $user->name,
                'email' => $user->email,
                'output' => 'Research report',
            ],
        ]);

        $originalData = $draft->data;

        // Paksa pelanggaran constraint dengan service_id yang tidak ada.
        $draft->update([
            'data' => array_merge($draft->data, [
                'service_id' => '00000000-0000-0000-0000-000000000000',
            ]),
        ]);

        $this->actingAs($user)
            ->post(route('hire.draft.submit', $draft), [
                'step' => 3,
                'deadline' => now()->addDays(7)->toDateString(),
                'budget' => 1000000,
                'willing_dp' => false,
            ])
            ->assertSessionHasErrors();

        $draft->refresh();

        $this->assertSame('draft', $draft->status);
        $this->assertDatabaseCount('projects', 0);

        Mail::assertNothingSent();
    }
}
