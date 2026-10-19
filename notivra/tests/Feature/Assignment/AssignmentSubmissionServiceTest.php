<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Jobs\CheckAssignmentDuplicateJob;
use App\Domains\Assignments\Services\AssignmentSubmissionService;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class AssignmentSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_valid_submission_stores_file_hash_and_updates_group(): void
    {
        Queue::fake();

        [$assignment, $group] = $this->createEligibleAssignmentAndGroup();
        $file = $this->createValidDocx();
        $expectedHash = hash_file('sha256', $file->getRealPath());

        $submission = app(AssignmentSubmissionService::class)
            ->submit($assignment, $group, $file);

        $this->assertInstanceOf(AssignmentSubmission::class, $submission);
        $this->assertSame($assignment->id, $submission->assignment_id);
        $this->assertSame($group->id, $submission->assignment_group_id);
        $this->assertSame('report.docx', $submission->original_filename);
        $this->assertSame($expectedHash, $submission->sha256);
        $this->assertSame('pending', $submission->duplicate_status);
        $this->assertSame([], $submission->metadata);
        $this->assertSame([], $submission->duplicate_findings);

        Storage::disk('local')->assertExists($submission->file_path);

        $this->assertStringStartsWith(
            "assignment-submissions/{$assignment->id}/{$group->id}/",
            $submission->file_path,
        );

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'sha256' => $expectedHash,
            'duplicate_status' => 'pending',
        ]);

        $this->assertSame('submitted', $group->fresh()->status);
        $this->assertNotNull($group->fresh()->submitted_at);

        Queue::assertPushed(
            CheckAssignmentDuplicateJob::class,
            fn(CheckAssignmentDuplicateJob $job): bool =>
            $job->submissionId === $submission->id
                && $job->queue === 'assignments',
        );
    }

    public function test_submission_is_rejected_when_assignment_is_not_published(): void
    {
        Queue::fake();

        [$assignment, $group] = $this->createEligibleAssignmentAndGroup([
            'status' => 'draft',
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(AssignmentSubmissionService::class)->submit(
                $assignment,
                $group,
                $this->createValidDocx(),
            );
        } finally {
            $this->assertSame([], Storage::disk('local')->allFiles());

            $this->assertDatabaseMissing('assignment_submissions', [
                'assignment_group_id' => $group->id,
            ]);

            Queue::assertNothingPushed();
        }
    }

    public function test_submission_is_rejected_when_group_has_incomplete_member(): void
    {
        Queue::fake();

        [$assignment, $group] = $this->createEligibleAssignmentAndGroup(
            verificationStatuses: ['completed', 'pending'],
        );

        try {
            app(AssignmentSubmissionService::class)->submit(
                $assignment,
                $group,
                $this->createValidDocx(),
            );

            $this->fail('Submission dengan anggota belum terverifikasi harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('group', $exception->errors());
        }

        $this->assertSame([], Storage::disk('local')->allFiles());

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_submission_is_rejected_when_group_does_not_belong_to_assignment(): void
    {
        Queue::fake();

        [$assignment, $group] = $this->createEligibleAssignmentAndGroup();

        $otherAssignment = Assignment::factory()->create([
            'status' => 'published',
            'due_at' => now()->addDay(),
        ]);

        try {
            app(AssignmentSubmissionService::class)->submit(
                $otherAssignment,
                $group,
                $this->createValidDocx(),
            );

            $this->fail('Grup milik tugas lain harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('group', $exception->errors());
        }

        $this->assertSame([], Storage::disk('local')->allFiles());

        $this->assertDatabaseMissing('assignment_submissions', [
            'assignment_group_id' => $group->id,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_submission_is_rejected_when_group_already_has_submission(): void
    {
        Queue::fake();

        [$assignment, $group] = $this->createEligibleAssignmentAndGroup();

        $existing = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'uploaded_by_member_id' => null,
            'file_path' => 'assignment-submissions/existing.docx',
            'original_filename' => 'existing.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'file_size' => 100,
            'sha256' => hash('sha256', 'existing document'),
            'duplicate_status' => 'pending',
            'metadata' => [],
            'duplicate_findings' => [],
            'submitted_at' => now(),
        ]);

        try {
            app(AssignmentSubmissionService::class)->submit(
                $assignment,
                $group,
                $this->createValidDocx(),
            );

            $this->fail('Submission kedua harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        }

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $existing->id,
        ]);

        $this->assertDatabaseCount('assignment_submissions', 1);
        $this->assertSame([], Storage::disk('local')->allFiles());

        Queue::assertNothingPushed();
    }

    private function createEligibleAssignmentAndGroup(
        array $assignmentOverrides = [],
        array $verificationStatuses = ['completed'],
    ): array {
        $assignment = Assignment::factory()->create(array_merge([
            'status' => 'published',
            'opens_at' => now()->subDay(),
            'due_at' => now()->addDay(),
        ], $assignmentOverrides));

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'ready_for_upload',
        ]);

        foreach ($verificationStatuses as $status) {
            $student = Student::factory()->create();

            $member = AssignmentGroupMember::query()->create([
                'assignment_id' => $assignment->id,
                'assignment_group_id' => $group->id,
                'student_id' => $student->id,
            ]);

            AssignmentVerification::query()->create([
                'assignment_group_member_id' => $member->id,
                'status' => $status,
                'answers' => [],
                'attempt_count' => 0,
            ]);
        }

        return [$assignment, $group];
    }

    private function createValidDocx(): UploadedFile
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP ext-zip tidak tersedia.');
        }

        $path = tempnam(sys_get_temp_dir(), 'assignment-service-');

        if ($path === false) {
            $this->fail('Tidak dapat membuat file sementara.');
        }

        $this->temporaryFiles[] = $path;

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            $this->fail('Tidak dapat membuat fixture DOCX.');
        }

        $zip->addFromString(
            '[Content_Types].xml',
            '<?xml version="1.0"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>',
        );

        $zip->addFromString(
            '_rels/.rels',
            '<?xml version="1.0"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>',
        );

        $zip->addFromString(
            'word/document.xml',
            '<?xml version="1.0"?>'
                . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                . '<w:body><w:p><w:r><w:t>Test submission</w:t></w:r></w:p></w:body>'
                . '</w:document>',
        );

        $zip->close();

        return new UploadedFile(
            $path,
            'report.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true,
        );
    }
}
