<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HireDraftMailFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_remains_successful_if_mail_fails(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->with(\Mockery::type('string'))
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 3,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'name' => $user->name,
                'email' => $user->email,
                'topic' => 'Mail failure test',
                'output' => 'Research report',
                'deadline' => now()->addDays(7)->toDateString(),
            ],
        ]);

        $response = $this->actingAs($user)
            ->post(route('hire.draft.submit', $draft), [
                'step' => 3,
                'service_id' => $service->id,
                'topic' => 'Mail failure test',
                'name' => $user->name,
                'email' => $user->email,
                'output' => 'Research report',
                'deadline' => now()->addDays(7)->toDateString(),
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertSame(
            'submitted',
            $draft->fresh()->status
        );

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'topic' => 'Mail failure test',
        ]);
    }
}
