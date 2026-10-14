<?php

namespace Tests\Feature\Hire;

use App\Models\HireDraft;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HireDraftFileStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_upload_is_stored_and_path_is_saved_to_draft(): void
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
                'topic' => 'File storage test',
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        $response = $this->actingAs($user)
            ->from(route('hire.apply', ['d' => $draft->id]))
            ->put(route('hire.draft.update', $draft), [
                'step' => 2,
                'next_step' => 3,
                'output' => 'Research report',
                'files' => UploadedFile::fake()->create(
                    'research.pdf',
                    500,
                    'application/pdf'
                ),
            ]);

        $response
            ->assertRedirect(route('hire.apply', ['d' => $draft->id]))
            ->assertSessionHas('success');

        $draft->refresh();

        $path = $draft->data['files'] ?? null;

        $this->assertNotNull($path);
        $this->assertStringStartsWith('drafts/attachments/', $path);
        Storage::disk('local')->assertExists($path);

        $this->assertSame(3, $draft->step);
        $this->assertSame('draft', $draft->status);
    }

    public function test_replacing_file_deletes_old_file_and_stores_new_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $oldPath = 'drafts/attachments/old-research.pdf';

        Storage::disk('local')->put(
            $oldPath,
            '%PDF-1.4 old test document'
        );

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 2,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'File replacement test',
                'name' => $user->name,
                'email' => $user->email,
                'files' => $oldPath,
            ],
        ]);

        $response = $this->actingAs($user)
            ->from(route('hire.apply', ['d' => $draft->id]))
            ->put(route('hire.draft.update', $draft), [
                'step' => 2,
                'next_step' => 3,
                'output' => 'Research report',
                'files' => UploadedFile::fake()->create(
                    'replacement.pdf',
                    400,
                    'application/pdf'
                ),
            ]);

        $response
            ->assertRedirect(route('hire.apply', ['d' => $draft->id]))
            ->assertSessionHas('success');

        $draft->refresh();

        $newPath = $draft->data['files'] ?? null;

        $this->assertNotNull($newPath);
        $this->assertNotSame($oldPath, $newPath);

        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);
    }

    public function test_invalid_replacement_does_not_delete_existing_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $service = Service::factory()->create();

        $oldPath = 'drafts/attachments/existing-research.pdf';

        Storage::disk('local')->put(
            $oldPath,
            '%PDF-1.4 existing document'
        );

        $draft = HireDraft::create([
            'user_id' => $user->id,
            'step' => 2,
            'status' => 'draft',
            'data' => [
                'service_id' => $service->id,
                'topic' => 'Invalid replacement test',
                'name' => $user->name,
                'email' => $user->email,
                'files' => $oldPath,
            ],
        ]);

        $response = $this->actingAs($user)
            ->from(route('hire.apply', ['d' => $draft->id]))
            ->put(route('hire.draft.update', $draft), [
                'step' => 2,
                'output' => 'Research report',
                'files' => UploadedFile::fake()->create(
                    'malicious.exe',
                    100,
                    'application/octet-stream'
                ),
            ]);

        $response
            ->assertRedirect(route('hire.apply', ['d' => $draft->id]))
            ->assertSessionHasErrors('files');

        $draft->refresh();

        $this->assertSame($oldPath, $draft->data['files']);

        Storage::disk('local')->assertExists($oldPath);

        $this->assertSame('draft', $draft->status);
        $this->assertSame(2, $draft->step);
    }
}
