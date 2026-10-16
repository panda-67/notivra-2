<?php

namespace Tests\Feature\Assignment;

use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class AssignmentSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var list<string>
     */
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_ready_group_can_submit_a_docx_and_group_status_changes(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        $group = $this->createGroup($assignment, 'ready_for_upload', [
            'completed',
            'completed',
        ]);

        $response = $this->postSubmission($assignment, $group);

        $response->assertRedirect(route('assignments.groups.show', [
            'slug' => $assignment->slug,
            'reference_code' => $group->reference_code,
        ]));

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'original_filename' => 'report.docx',
        ]);

        $group->refresh();

        $this->assertSame('submitted', $group->status);
        $this->assertNotNull($group->submitted_at);

        $submission = AssignmentSubmission::query()
            ->where('assignment_group_id', $group->id)
            ->firstOrFail();

        Storage::disk('local')->assertExists($submission->file_path);

        $this->assertSame(64, strlen($submission->sha256));
        $this->assertSame('pending', $submission->duplicate_status);
    }

    public function test_group_that_is_not_ready_cannot_submit(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        $group = $this->createGroup(
            $assignment,
            'verification_in_progress',
            ['completed'],
        );

        $this->postSubmission($assignment, $group)
            ->assertSessionHasErrors('group');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        $this->assertSame(
            'verification_in_progress',
            $group->fresh()->status,
        );

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_submission_is_rejected_if_any_member_is_incomplete(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        // Deliberately inconsistent state: ready group, one pending member.
        $group = $this->createGroup($assignment, 'ready_for_upload', [
            'completed',
            'pending',
        ]);

        $this->postSubmission($assignment, $group)
            ->assertSessionHasErrors('group');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        $this->assertSame(
            'ready_for_upload',
            $group->fresh()->status,
        );

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_group_cannot_submit_twice(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        $group = $this->createGroup(
            $assignment,
            'ready_for_upload',
            ['completed'],
        );

        $this->postSubmission($assignment, $group)
            ->assertRedirect();

        $this->assertDatabaseCount('assignment_submissions', 1);

        $submission = AssignmentSubmission::query()
            ->where('assignment_group_id', $group->id)
            ->firstOrFail();

        $originalPath = $submission->file_path;

        $this->postSubmission($assignment, $group)
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('assignment_submissions', 1);

        $this->assertSame('submitted', $group->fresh()->status);

        // The existing submission must remain intact.
        Storage::disk('local')->assertExists($originalPath);
    }

    public function test_non_docx_file_is_rejected(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        $group = $this->createGroup(
            $assignment,
            'ready_for_upload',
            ['completed'],
        );

        $response = $this->post(
            route('assignments.submission.store', [
                'slug' => $assignment->slug,
                'reference_code' => $group->reference_code,
            ]),
            [
                'file' => UploadedFile::fake()->create(
                    'report.pdf',
                    100,
                    'application/pdf',
                ),
            ],
        );

        $response->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_file_with_docx_extension_but_invalid_content_is_rejected(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        $group = $this->createGroup(
            $assignment,
            'ready_for_upload',
            ['completed'],
        );

        $response = $this->post(
            route('assignments.submission.store', [
                'slug' => $assignment->slug,
                'reference_code' => $group->reference_code,
            ]),
            [
                'file' => UploadedFile::fake()->createWithContent(
                    'report.docx',
                    'Ini bukan dokumen DOCX atau arsip ZIP yang valid.',
                ),
            ],
        );

        $response->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        $this->assertSame(
            'ready_for_upload',
            $group->fresh()->status,
        );

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_draft_assignment_is_not_accessible_through_public_group_listing(): void
    {
        $assignment = Assignment::factory()->draft()->create();

        $this->get(route('assignments.groups.index', [
            'slug' => $assignment->slug,
        ]))->assertNotFound();
    }

    public function test_published_assignment_is_accessible_through_public_group_listing(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('assignments.groups.index', [
            'slug' => $assignment->slug,
        ]))->assertOk();
    }

    public function test_closed_assignment_rejects_submission(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'closed',
            'due_at' => now()->addDay(),
        ]);

        $group = $this->createGroup(
            $assignment,
            'ready_for_upload',
            ['completed'],
        );

        $this->postSubmission($assignment, $group)
            ->assertSessionHasErrors('assignment');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_submission_is_rejected_after_due_at(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->subSecond(),
        ]);

        $group = $this->createGroup(
            $assignment,
            'ready_for_upload',
            ['completed'],
        );

        $this->postSubmission($assignment, $group)
            ->assertSessionHasErrors('assignment');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_docx_larger_than_five_megabytes_is_rejected(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        $group = $this->createGroup(
            $assignment,
            'ready_for_upload',
            ['completed'],
        );

        $response = $this->post(
            route('assignments.submission.store', [
                'slug' => $assignment->slug,
                'reference_code' => $group->reference_code,
            ]),
            [
                'file' => UploadedFile::fake()->create(
                    'report.docx',
                    5121,
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ),
            ],
        );

        $response->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    private function createValidDocx(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'assignment-');

        if ($path === false) {
            throw new \RuntimeException(
                'Tidak dapat membuat file sementara untuk DOCX.',
            );
        }

        $this->temporaryFiles[] = $path;

        $zip = new ZipArchive();

        if (
            $zip->open(
                $path,
                ZipArchive::CREATE | ZipArchive::OVERWRITE,
            ) !== true
        ) {
            throw new \RuntimeException(
                'Tidak dapat membuat fixture DOCX.',
            );
        }

        try {
            $zip->addFromString(
                '[Content_Types].xml',
                '<?xml version="1.0" encoding="UTF-8"?>'
                    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                    . '<Default Extension="xml" ContentType="application/xml"/>'
                    . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
                    . '</Types>',
            );

            $zip->addFromString(
                '_rels/.rels',
                '<?xml version="1.0" encoding="UTF-8"?>'
                    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
                    . '</Relationships>',
            );

            $zip->addFromString(
                'word/document.xml',
                '<?xml version="1.0" encoding="UTF-8"?>'
                    . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                    . '<w:body><w:p><w:r><w:t>Test submission</w:t></w:r></w:p>'
                    . '<w:sectPr/></w:body></w:document>',
            );
        } finally {
            $zip->close();
        }

        return new UploadedFile(
            $path,
            'report.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true,
        );
    }

    private function postSubmission(
        Assignment $assignment,
        AssignmentGroup $group,
    ) {
        return $this->post(
            route('assignments.submission.store', [
                'slug' => $assignment->slug,
                'reference_code' => $group->reference_code,
            ]),
            [
                'file' => $this->createValidDocx(),
            ],
        );
    }

    private function createGroup(
        Assignment $assignment,
        string $status,
        array $verificationStatuses,
    ): AssignmentGroup {
        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => $status,
        ]);

        foreach ($verificationStatuses as $verificationStatus) {
            $student = Student::factory()->create();

            $member = AssignmentGroupMember::query()->create([
                'assignment_id' => $assignment->id,
                'assignment_group_id' => $group->id,
                'student_id' => $student->id,
            ]);

            AssignmentVerification::query()->create([
                'assignment_group_member_id' => $member->id,
                'status' => $verificationStatus,
                'answers' => [],
                'attempt_count' => 0,
            ]);
        }

        return $group;
    }
}
