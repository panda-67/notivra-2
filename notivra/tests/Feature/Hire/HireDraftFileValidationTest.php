<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HireDraftFileValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_two_rejects_unsupported_file_type(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 2,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'File validation test',
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        $this->actingAs($user)
            ->from(route('hire.apply', ['d' => $draft->id]))
            ->put(route('hire.draft.update', $draft), [
                'step' => 2,
                'output' => 'Research report',
                'files' => UploadedFile::fake()->create(
                    'malicious.exe',
                    10,
                    'application/octet-stream'
                ),
            ])
            ->assertSessionHasErrors('files');

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
            'step' => 2,
        ]);

        $this->assertSame(
            'File validation test',
            $draft->fresh()->data['topic']
        );
    }

    public function test_step_two_rejects_file_larger_than_allowed_size(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 2,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Oversized file test',
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        $this->actingAs($user)
            ->from(route('hire.apply', ['d' => $draft->id]))
            ->put(route('hire.draft.update', $draft), [
                'step' => 2,
                'output' => 'Research report',
                'files' => UploadedFile::fake()->create(
                    'large-document.pdf',
                    3000,
                    'application/pdf'
                ),
            ])
            ->assertSessionHasErrors('files');

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
            'step' => 2,
        ]);

        $this->assertSame(
            'Oversized file test',
            $draft->fresh()->data['topic']
        );
    }

    public function test_step_two_accepts_allowed_file_type_within_size_limit(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 2,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Valid file test',
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        $this->actingAs($user)
            ->put(route('hire.draft.update', $draft), [
                'step' => 2,
                'output' => 'Research report',
                'files' => UploadedFile::fake()->create(
                    'document.pdf',
                    500,
                    'application/pdf'
                ),
            ])
            ->assertSessionDoesntHaveErrors('files');

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);
    }
}
