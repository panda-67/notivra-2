<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HireDraftFileSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_attachment_prevents_submission(): void
    {
        Storage::fake('local');

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
                'topic' => 'Attachment failure test',
                'output' => 'Research report',
                'deadline' => now()->addDays(7)->toDateString(),
                'files' => 'drafts/attachments/missing.pdf',
            ],
        ]);

        $response = $this->actingAs($user)
            ->from(route('hire.apply', ['d' => $draft->id]))
            ->post(route('hire.draft.submit', $draft), [
                'step' => 3,
                'service_id' => $service->id,
                'topic' => 'Attachment failure test',
                'name' => $user->name,
                'email' => $user->email,
                'output' => 'Research report',
                'deadline' => now()->addDays(7)->toDateString(),
            ]);

        $response->assertSessionHasErrors('files');

        $this->assertSame(
            'draft',
            $draft->fresh()->status
        );

        $this->assertDatabaseMissing('projects', [
            'user_id' => $user->id,
        ]);
    }
}
